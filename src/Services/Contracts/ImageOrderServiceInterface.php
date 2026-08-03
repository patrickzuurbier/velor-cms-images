<?php

declare(strict_types=1);

namespace Velor\Images\Services\Contracts;

use Velor\Images\Models\Image;

interface ImageOrderServiceInterface
{
    public function nextSortOrder(?string $categoryId): int;

    public function moveToCategory(Image $image, ?string $categoryId): void;

    public function moveToCategoryAt(Image $image, ?string $categoryId, int $sortOrder): void;
}
