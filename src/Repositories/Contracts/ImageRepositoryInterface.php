<?php

declare(strict_types=1);

namespace Velor\Images\Repositories\Contracts;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Velor\Images\Models\Image;

interface ImageRepositoryInterface
{
    /**
     * @return EloquentBuilder<Image>
     */
    public function indexQuery(?string $category): EloquentBuilder;

    /**
     * @return Collection<int, Image>
     */
    public function orderedForPicker(): Collection;

    /**
     * @return array<string, bool>
     */
    public function existingIds(): array;

    /**
     * @param int $chunkSize
     * @param Closure(EloquentCollection<int, Image>): void $callback
     */
    public function chunkForRegeneration(int $chunkSize, Closure $callback): void;

    /**
     * @return array<int, string>
     */
    public function orderedIdsForCategory(?string $categoryId, ?string $except = null): array;
}
