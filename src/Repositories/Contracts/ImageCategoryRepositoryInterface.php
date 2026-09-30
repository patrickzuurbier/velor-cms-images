<?php

declare(strict_types=1);

namespace Velor\Images\Repositories\Contracts;

use Velor\Images\Models\Image;
use Velor\Images\Models\ImageCategory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface ImageCategoryRepositoryInterface
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): ImageCategory;

    /**
     * @param ImageCategory $imageCategory
     * @param array<string, mixed> $attributes
     */
    public function update(ImageCategory $imageCategory, array $attributes): ImageCategory;

    public function delete(ImageCategory $imageCategory): void;

    /**
     * @return EloquentCollection<int, Image>
     */
    public function orderedImages(ImageCategory $imageCategory): EloquentCollection;

    public function exists(string $id): bool;

    /**
     * @return array<int, ImageCategory>
     */
    public function orderedByName(): array;
}
