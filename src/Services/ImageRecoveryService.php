<?php

declare(strict_types=1);

namespace Velor\Images\Services;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use Velor\Images\Models\Image;
use Velor\Images\Repositories\Contracts\ImageRepositoryInterface;
use Velor\Images\Services\Contracts\ImageOrderServiceInterface;
use Velor\Images\Services\Contracts\ImageRecoveryServiceInterface;

class ImageRecoveryService implements ImageRecoveryServiceInterface
{
    public function __construct(
        protected FilesystemFactory $filesystem,
        protected ImageOrderServiceInterface $imageOrderService,
        protected ImageRepositoryInterface $imageRepository,
    ) {
    }

    public function recover(bool $dryRun = false, ?callable $onSkipped = null): int
    {
        $disk = $this->filesystem->disk('s3');
        $originals = $this->originals($disk);
        $existingIds = $this->imageRepository->existingIds();
        $recovered = 0;
        $nextSortOrder = $this->imageOrderService->nextSortOrder(null);

        foreach ($originals as $imageId => $originalPath) {
            if (isset($existingIds[$imageId])) {
                continue;
            }

            try {
                $image = $this->image($disk, $imageId, $originalPath, $nextSortOrder);

                if (! $dryRun) {
                    $image->save();
                }

                $existingIds[$imageId] = true;
                $nextSortOrder++;
                $recovered++;
            } catch (Throwable $exception) {
                if ($onSkipped !== null) {
                    $onSkipped("{$originalPath}: {$exception->getMessage()}");
                }
            }
        }

        return $recovered;
    }

    /**
     * @return array<string, string>
     */
    protected function originals(Filesystem $disk): array
    {
        $originals = [];

        foreach ($disk->allFiles('images') as $path) {
            if (preg_match('#^images/([^/]+)/original\.([^/]+)$#', $path, $matches) !== 1) {
                continue;
            }

            $imageId = $matches[1];

            if (Str::isUuid($imageId) && ! isset($originals[$imageId])) {
                $originals[$imageId] = $path;
            }
        }

        ksort($originals);

        return $originals;
    }

    protected function image(Filesystem $disk, string $imageId, string $originalPath, int $sortOrder): Image
    {
        $original = $this->fileMetaData($disk, $originalPath);
        $formats = [];

        foreach ($disk->files("images/{$imageId}") as $path) {
            if ($path === $originalPath) {
                continue;
            }

            $format = pathinfo($path, PATHINFO_FILENAME);
            $formats[$format] = $this->fileMetaData($disk, $path);
        }

        ksort($formats);

        $image = new Image([
            'filename'          => basename($originalPath),
            'name'              => 'Recovered image '.Str::substr($imageId, 0, 8),
            'image_category_id' => null,
            'sort_order'        => $sortOrder,
            'meta_data'         => array_filter([
                'original' => $original,
                'formats'  => $formats,
            ]),
        ]);
        $image->id = $imageId;

        try {
            $timestamp = CarbonImmutable::createFromTimestamp($disk->lastModified($originalPath));
            $image->setCreatedAt($timestamp);
            $image->setUpdatedAt($timestamp);
        } catch (Throwable) {
            // Some filesystem drivers do not expose object timestamps.
        }

        return $image;
    }

    /**
     * @return array{extension: string, mime_type: string, size: int, width: int, height: int}
     */
    protected function fileMetaData(Filesystem $disk, string $path): array
    {
        $contents = $disk->get($path);

        if (! is_string($contents)) {
            throw new RuntimeException('Object could not be read.');
        }

        $dimensions = getimagesizefromstring($contents);

        if ($dimensions === false) {
            throw new RuntimeException('Object is not a readable image.');
        }

        return [
            'extension' => strtolower(pathinfo($path, PATHINFO_EXTENSION)),
            'mime_type' => $dimensions['mime'],
            'size'      => strlen($contents),
            'width'     => (int) $dimensions[0],
            'height'    => (int) $dimensions[1],
        ];
    }
}
