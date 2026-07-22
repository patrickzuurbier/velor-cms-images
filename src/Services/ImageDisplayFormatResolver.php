<?php

declare(strict_types=1);

namespace Velor\Images\Services;

use Velor\Images\Models\Image;
use Velor\Images\Services\Contracts\ImageDisplayFormatResolverInterface;

class ImageDisplayFormatResolver implements ImageDisplayFormatResolverInterface
{
    public function index(Image $image): string
    {
        return 'thumbnail';
    }

    public function cms(Image $image): string
    {
        return $this->hasFormat($image, 'cms') ? 'cms' : 'full';
    }

    protected function hasFormat(Image $image, string $format): bool
    {
        $metaData = $image->metaData();

        return isset($metaData['formats'])
            && is_array($metaData['formats'])
            && array_key_exists($format, $metaData['formats']);
    }
}
