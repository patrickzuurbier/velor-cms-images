<?php

declare(strict_types=1);

namespace Velor\Images\Repositories;

use Velor\Images\Models\ImageCategory;
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
