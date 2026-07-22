<?php

declare(strict_types=1);

namespace Velor\Images\Services;

use Velor\Images\Services\Contracts\ImagePathGeneratorInterface;
use Velor\Images\Models\Image;

class ImagePathGenerator implements ImagePathGeneratorInterface
{
    public function directory(string $imageId): string
    {
        return 'images/' . $imageId;
    }

    public function original(Image $image): string
    {
        return $this->directory((string) $image->getKey()) . '/original.' . $this->originalExtension($image);
    }

    public function format(Image $image, string $format): string
    {
        return $this->directory((string) $image->getKey()) . '/' . $format . '.webp';
    }

    protected function originalExtension(Image $image): string
    {
        $metaData = $image->metaData();
        $extension = $metaData['original']['extension'] ?? null;

        return is_string($extension) && $extension !== ''
            ? $extension
            : pathinfo($image->filename, PATHINFO_EXTENSION);
    }
}
