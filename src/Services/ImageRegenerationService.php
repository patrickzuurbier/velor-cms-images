<?php

declare(strict_types=1);

namespace Velor\Images\Services;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Velor\Images\Repositories\Contracts\ImageRepositoryInterface;
use Velor\Images\Services\Contracts\ImageFormatGeneratorInterface;
use Velor\Images\Services\Contracts\ImageRegenerationServiceInterface;

class ImageRegenerationService implements ImageRegenerationServiceInterface
{
    public function __construct(
        protected ImageFormatGeneratorInterface $formatGenerator,
        protected ImageRepositoryInterface $imageRepository,
    ) {
    }

    public function regenerate(int $chunkSize = 100): int
    {
        $regenerated = 0;

        $this->imageRepository->chunkForRegeneration(
            chunkSize: $chunkSize,
            callback: function (EloquentCollection $images) use (&$regenerated): void {
                foreach ($images as $image) {
                    $this->formatGenerator->regenerate($image);
                    $regenerated++;
                }
            },
        );

        return $regenerated;
    }
}
