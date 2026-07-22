<?php

declare(strict_types=1);

namespace Velor\Images\Services;

use Velor\Images\Services\Contracts\ImageFormatGeneratorInterface;
use Velor\Images\Services\Contracts\ImagePathGeneratorInterface;
use Velor\Images\Models\Image;
use GdImage;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class ImageFormatGenerator implements ImageFormatGeneratorInterface
{
    public function __construct(
        protected Repository $config,
        protected FilesystemFactory $filesystem,
        protected ImagePathGeneratorInterface $pathGenerator,
    ) {
    }

    public function generate(Image $image, UploadedFile $file): void
    {
        $path = $file->getRealPath();

        if (! is_string($path)) {
            throw new RuntimeException('Uploaded image path could not be resolved.');
        }

        $this->generateFromPath($image, $path, (string) $file->getMimeType());
    }

    public function regenerate(Image $image): void
    {
        $contents = $this->disk()->get($this->pathGenerator->original($image));
        $temporaryFile = tempnam(sys_get_temp_dir(), 'image-original-');

        if ($temporaryFile === false) {
            throw new RuntimeException('Could not create a temporary image file.');
        }

        try {
            file_put_contents($temporaryFile, $contents);

            $this->generateFromPath(
                image: $image,
                path: $temporaryFile,
                mimeType: $this->originalMimeType($image),
            );
        } finally {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }

    protected function generateFromPath(Image $image, string $path, string $mimeType): void
    {
        $this->deleteGeneratedFormats($image);

        $formats = [];

        foreach ($this->formats() as $name => $format) {
            $source = $this->createSourceImage($path, $mimeType);
            $resized = $this->resize($source, $this->originalWidth($image), $this->originalHeight($image), $format);
            $formats[$name] = $this->storeWebp($image, $name, $resized, $this->quality($format));

            imagedestroy($source);
            imagedestroy($resized);
        }

        $metaData = $image->metaData();
        $metaData['formats'] = $formats;
        $image->setMetaData($metaData);
        $image->save();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function formats(): array
    {
        $formats = $this->config->get('velor-images.formats', []);

        return is_array($formats) ? $formats : [];
    }

    protected function createSourceImage(string $path, string $mimeType): GdImage
    {
        $image = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png'  => imagecreatefrompng($path),
            'image/gif'  => imagecreatefromgif($path),
            'image/webp' => imagecreatefromwebp($path),
            default      => false,
        };

        if (! $image instanceof GdImage) {
            throw new RuntimeException("Unsupported image mime type [{$mimeType}].");
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        return $image;
    }

    protected function deleteGeneratedFormats(Image $image): void
    {
        $directory = $this->pathGenerator->directory((string) $image->getKey());
        $original = $this->pathGenerator->original($image);
        $files = $this->disk()->files($directory);

        foreach ($files as $file) {
            if ($file !== $original) {
                $this->disk()->delete($file);
            }
        }
    }

    /**
     * @param array<string, mixed> $format
     */
    protected function resize(GdImage $source, int $sourceWidth, int $sourceHeight, array $format): GdImage
    {
        $targetWidth = $this->dimension($format['width'] ?? null);
        $targetHeight = $this->dimension($format['height'] ?? null);
        $upscale = (bool) ($format['upscale'] ?? false);

        if ($targetWidth === null && $targetHeight === null) {
            $targetWidth = $sourceWidth;
            $targetHeight = $sourceHeight;
        }

        [$width, $height, $sourceX, $sourceY, $copyWidth, $copyHeight] = $this->dimensions(
            $sourceWidth,
            $sourceHeight,
            $targetWidth,
            $targetHeight,
            (string) ($format['fit'] ?? 'contain'),
            $upscale,
        );

        /** @var int<1, max> $width */
        $width = max(1, $width);
        /** @var int<1, max> $height */
        $height = max(1, $height);

        $target = imagecreatetruecolor($width, $height);
        imagealphablending($target, false);
        imagesavealpha($target, true);

        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            $sourceX,
            $sourceY,
            $width,
            $height,
            $copyWidth,
            $copyHeight,
        );

        return $target;
    }

    /**
     * @return array{0: int, 1: int, 2: int, 3: int, 4: int, 5: int}
     */
    protected function dimensions(
        int $sourceWidth,
        int $sourceHeight,
        ?int $targetWidth,
        ?int $targetHeight,
        string $fit,
        bool $upscale,
    ): array {
        if ($targetWidth === null) {
            $targetWidth = (int) round($sourceWidth * ((float) $targetHeight / $sourceHeight));
        }

        if ($targetHeight === null) {
            $targetHeight = (int) round($sourceHeight * ((float) $targetWidth / $sourceWidth));
        }

        if ($fit === 'cover') {
            $scale = max($targetWidth / $sourceWidth, $targetHeight / $sourceHeight);

            if (! $upscale) {
                $scale = min(1.0, $scale);
                $targetWidth = min($targetWidth, $sourceWidth);
                $targetHeight = min($targetHeight, $sourceHeight);
            }

            $copyWidth = (int) round($targetWidth / $scale);
            $copyHeight = (int) round($targetHeight / $scale);

            return [
                $targetWidth,
                $targetHeight,
                max(0, (int) floor(($sourceWidth - $copyWidth) / 2)),
                max(0, (int) floor(($sourceHeight - $copyHeight) / 2)),
                min($sourceWidth, $copyWidth),
                min($sourceHeight, $copyHeight),
            ];
        }

        $scale = min($targetWidth / $sourceWidth, $targetHeight / $sourceHeight);

        if (! $upscale) {
            $scale = min(1.0, $scale);
        }

        return [
            max(1, (int) round($sourceWidth * $scale)),
            max(1, (int) round($sourceHeight * $scale)),
            0,
            0,
            $sourceWidth,
            $sourceHeight,
        ];
    }

    protected function dimension(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        return max(1, (int) $value);
    }

    /**
     * @param array<string, mixed> $format
     */
    protected function quality(array $format): int
    {
        return max(1, min(100, (int) ($format['quality'] ?? 85)));
    }

    /**
     * @return array{extension: string, mime_type: string, size: int, width: int, height: int}
     */
    protected function storeWebp(Image $image, string $format, GdImage $gdImage, int $quality): array
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'image-format-');

        if ($temporaryFile === false) {
            throw new RuntimeException('Could not create a temporary image file.');
        }

        try {
            if (! imagewebp($gdImage, $temporaryFile, $quality)) {
                throw new RuntimeException("Could not generate WebP image format [{$format}].");
            }

            $contents = file_get_contents($temporaryFile);

            if ($contents === false) {
                throw new RuntimeException("Could not read generated image format [{$format}].");
            }

            $this->filesystem->disk('s3')->put(
                $this->pathGenerator->format($image, $format),
                $contents,
            );

            return [
                'extension' => 'webp',
                'mime_type' => 'image/webp',
                'size'      => strlen($contents),
                'width'     => imagesx($gdImage),
                'height'    => imagesy($gdImage),
            ];
        } finally {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }
        }
    }

    protected function originalWidth(Image $image): int
    {
        $metaData = $image->metaData();

        return max(1, (int) ($metaData['original']['width'] ?? 1));
    }

    protected function originalHeight(Image $image): int
    {
        $metaData = $image->metaData();

        return max(1, (int) ($metaData['original']['height'] ?? 1));
    }

    protected function originalMimeType(Image $image): string
    {
        $metaData = $image->metaData();
        $mimeType = $metaData['original']['mime_type'] ?? null;

        return is_string($mimeType) && $mimeType !== ''
            ? $mimeType
            : 'image/jpeg';
    }

    protected function disk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = $this->filesystem->disk('s3');

        return $disk;
    }
}
