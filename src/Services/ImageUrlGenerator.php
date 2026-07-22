<?php

declare(strict_types=1);

namespace Velor\Images\Services;

use Velor\Images\Services\Contracts\ImagePathGeneratorInterface;
use Velor\Images\Services\Contracts\ImageUrlGeneratorInterface;
use Velor\Images\Models\Image;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;

class ImageUrlGenerator implements ImageUrlGeneratorInterface
{
    public function __construct(
        protected FilesystemManager $filesystem,
        protected ImagePathGeneratorInterface $pathGenerator,
    ) {
    }

    public function original(Image $image): string
    {
        return $this->disk()->url($this->pathGenerator->original($image));
    }

    public function format(Image $image, string $format): string
    {
        return $this->disk()->url($this->pathGenerator->format($image, $format));
    }

    protected function disk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = $this->filesystem->disk('s3');

        return $disk;
    }
}
