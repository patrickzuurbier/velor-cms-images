<?php

declare(strict_types=1);

namespace Velor\Images\Repositories\Contracts;

use App\Models\Image;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface ImageManagementRepositoryInterface
{
    /**
     * @return array<int, string>
     */
    public function resourceTypes(): array;

    /**
     * @return Builder<Image>
     */
    public function indexQuery(?string $resourceType): Builder;

    /**
     * @return Collection<int, Image>
     */
    public function pickerImages(): Collection;

    /**
     * @param Image $image
     * @param array{name: string} $attributes
     */
    public function update(Image $image, array $attributes): Image;

    public function isUnused(Image $image): bool;
}
