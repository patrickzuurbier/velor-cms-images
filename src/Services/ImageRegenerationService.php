<?php

declare(strict_types=1);

namespace Velor\Images\Services;

use Velor\Images\Models\Image;
use Velor\Images\Services\Contracts\ImageFormatGeneratorInterface;
use Velor\Images\Services\Contracts\ImageRegenerationServiceInterface;

class ImageRegenerationService implements ImageRegenerationServiceInterface
{
    public function __construct(
        protected ImageFormatGeneratorInterface $formatGenerator,
    ) {
    }

    public function regenerate(int $chunkSize = 100): int
    {
        $regenerated = 0;

        Image::query()
            ->orderBy('id')
            ->chunk(max(1, $chunkSize), function ($images) use (&$regenerated): void {
                foreach ($images as $image) {
                    $this->formatGenerator->regenerate($image);
                    $regenerated++;
                }
            });

        return $regenerated;
    }
}
