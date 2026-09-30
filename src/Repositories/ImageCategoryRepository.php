<?php

declare(strict_types=1);

namespace Velor\Images\Repositories;

use Velor\Images\Models\Image;
use Velor\Images\Models\ImageCategory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Velor\Images\Repositories\Contracts\ImageCategoryRepositoryInterface;

/**
 * @extends AbstractRepository<ImageCategory>
 */
class ImageCategoryRepository extends AbstractRepository implements ImageCategoryRepositoryInterface
{
    /**
     * @var class-string<ImageCategory>
     */
    protected string $model = ImageCategory::class;

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): ImageCategory
    {
        return $this->query()->create($attributes);
    }

    /**
     * @param ImageCategory $imageCategory
     * @param array<string, mixed> $attributes
     */
    public function update(ImageCategory $imageCategory, array $attributes): ImageCategory
    {
        $imageCategory->fill($attributes);
        $imageCategory->save();

        return $imageCategory;
    }

    public function delete(ImageCategory $imageCategory): void
    {
        $imageCategory->delete();
    }

    /**
     * @return EloquentCollection<int, Image>
     */
    public function orderedImages(ImageCategory $imageCategory): EloquentCollection
    {
        return $imageCategory->images()
            ->orderBy('sort_order')
            ->get();
    }

    public function exists(string $id): bool
    {
        return $this->query()->whereKey($id)->exists();
    }

    /**
     * @return array<int, ImageCategory>
     */
    public function orderedByName(): array
    {
        return $this->query()
            ->orderBy('name')
            ->get()
            ->all();
    }
}
