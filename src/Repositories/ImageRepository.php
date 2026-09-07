<?php

declare(strict_types=1);

namespace Velor\Images\Repositories;

use Closure;
use Illuminate\Support\Collection;
use Velor\Images\Models\Image;
use Velor\Images\Repositories\Contracts\ImageRepositoryInterface;

/**
 * @extends AbstractRepository<Image>
 */
class ImageRepository extends AbstractRepository implements ImageRepositoryInterface
{
    /**
     * @var class-string<Image>
     */
    protected string $model = Image::class;

    /**
     * @return Collection<int, Image>
     */
    public function orderedForPicker(): Collection
    {
        /** @var Collection<int, Image> $images */
        $images = $this->query()
            ->orderBy('name')
            ->get();

        return $images;
    }

    /**
     * @return array<string, bool>
     */
    public function existingIds(): array
    {
        /** @var array<string, bool> $ids */
        $ids = $this->query()
            ->pluck('id')
            ->mapWithKeys(static fn (mixed $id): array => [(string) $id => true])
            ->all();

        return $ids;
    }

    public function chunkForRegeneration(int $chunkSize, Closure $callback): void
    {
        $this->query()
            ->orderBy('id')
            ->chunk(max(1, $chunkSize), $callback);
    }

    /**
     * @return array<int, string>
     */
    public function orderedIdsForCategory(?string $categoryId, ?string $except = null): array
    {
        return $this->query()
            ->where('image_category_id', $categoryId)
            ->when($except !== null, fn ($query) => $query->where('id', '<>', $except))
            ->orderBy('sort_order')
            ->pluck('id')
            ->map(static fn (mixed $id): string => (string) $id)
            ->all();
    }
}
