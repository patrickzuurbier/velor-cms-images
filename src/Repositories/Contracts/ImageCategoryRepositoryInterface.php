<?php

declare(strict_types=1);

namespace Velor\Images\Repositories\Contracts;

use Velor\Images\Models\ImageCategory;

interface ImageCategoryRepositoryInterface
{
    public function exists(string $id): bool;

    /**
     * @return array<int, ImageCategory>
     */
    public function orderedByName(): array;
}
