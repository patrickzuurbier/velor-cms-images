<?php

declare(strict_types=1);

namespace Velor\Images\Repositories\Contracts;

use Closure;
use Illuminate\Support\Collection;
use Velor\Images\Models\Image;

interface ImageRepositoryInterface
{
    /**
     * @return Collection<int, Image>
     */
    public function orderedForPicker(): Collection;

    /**
     * @return array<string, bool>
     */
    public function existingIds(): array;

    public function chunkForRegeneration(int $chunkSize, Closure $callback): void;

    /**
     * @return array<int, string>
     */
    public function orderedIdsForCategory(?string $categoryId, ?string $except = null): array;
}
