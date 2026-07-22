<?php

declare(strict_types=1);

namespace Velor\Images\Services;

use Velor\Images\Services\Contracts\ImageFormatGeneratorInterface;
use Velor\Images\Services\Contracts\ImageOrderServiceInterface;
use Velor\Images\Services\Contracts\ImagePathGeneratorInterface;
use Velor\Images\Services\Contracts\ImageUploadServiceInterface;
use Velor\Images\Models\Image;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ImageUploadService implements ImageUploadServiceInterface
{
    public function __construct(
        protected FilesystemFactory $filesystem,
        protected ImagePathGeneratorInterface $pathGenerator,
        protected ImageFormatGeneratorInterface $formatGenerator,
        protected ImageOrderServiceInterface $imageOrderService,
    ) {
    }

    public function upload(UploadedFile $file, ?string $categoryId = null): Image
    {
        $dimensions = getimagesize($file->getRealPath() ?: '');

        if ($dimensions === false) {
            throw new RuntimeException('Uploaded file is not a readable image.');
        }

        $imageId = (string) Str::uuid();
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');

        $image = new Image([
            'filename'          => $file->getClientOriginalName(),
            'name'              => $this->name($file),
            'image_category_id' => $categoryId,
            'sort_order'        => $this->imageOrderService->nextSortOrder($categoryId),
            'description'       => null,
            'meta_data'         => [
                'original' => [
                    'extension' => $extension,
                    'mime_type' => (string) $file->getMimeType(),
                    'size'      => $file->getSize() ?: 0,
                    'width'     => (int) $dimensions[0],
                    'height'    => (int) $dimensions[1],
                ],
            ],
        ]);
        $image->id = $imageId;
        $image->save();

        try {
            $this->filesystem->disk('s3')->putFileAs(
                $this->pathGenerator->directory($imageId),
                $file,
                'original.' . $extension,
            );

            $this->formatGenerator->generate($image, $file);
        } catch (Throwable $exception) {
            $this->filesystem->disk('s3')->deleteDirectory($this->pathGenerator->directory($imageId));
            $image->delete();

            throw $exception;
        }

        return $image;
    }

    public function delete(Image $image): void
    {
        $this->filesystem->disk('s3')->deleteDirectory($this->pathGenerator->directory((string) $image->getKey()));
        $image->delete();
    }

    protected function name(UploadedFile $file): string
    {
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        return Str::headline($filename);
    }
}
