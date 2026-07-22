<?php

declare(strict_types=1);

namespace Velor\Images\Services;

use Velor\Images\Models\Image;
use Velor\Images\Services\Contracts\ImageOrderServiceInterface;
use Illuminate\Database\ConnectionInterface;

class ImageOrderService implements ImageOrderServiceInterface
{
    protected const TEMPORARY_ORDER_OFFSET = 1000000;

    public function __construct(
        protected ConnectionInterface $database,
    ) {
    }

    public function nextSortOrder(?string $categoryId): int
    {
        $query = Image::query()
            ->where('image_category_id', $categoryId);

        return ((int) $query->max('sort_order')) + 1;
    }

    public function moveToCategory(Image $image, ?string $categoryId): void
    {
        $this->moveToCategoryAt($image, $categoryId, $this->nextSortOrder($categoryId));
    }

    public function moveToCategoryAt(Image $image, ?string $categoryId, int $sortOrder): void
    {
        $currentCategoryId = $image->getAttribute('image_category_id');

        $this->database->transaction(function () use ($image, $categoryId, $sortOrder, $currentCategoryId): void {
            $targetIds = $this->orderedIds($categoryId, except: (string) $image->getKey());
            $targetOrder = max(1, min($sortOrder, count($targetIds) + 1));

            array_splice($targetIds, $targetOrder - 1, 0, [(string) $image->getKey()]);

            $image->setAttribute('image_category_id', $categoryId);
            $image->setAttribute('sort_order', 0);
            $image->save();

            if ($currentCategoryId !== $categoryId) {
                $this->rewriteSortOrders($this->orderedIds(is_string($currentCategoryId) ? $currentCategoryId : null));
            }

            $this->rewriteSortOrders($targetIds);
        });
    }

    /**
     * @param array<int, string> $imageIds
     */
    public function reorderVisible(?string $categoryId, array $imageIds): void
    {
        $imageIds = array_values(array_unique($imageIds));

        if ($imageIds === []) {
            return;
        }

        $this->database->transaction(function () use ($categoryId, $imageIds): void {
            $images = Image::query()
                ->where('image_category_id', $categoryId)
                ->whereKey($imageIds)
                ->orderBy('sort_order')
                ->get();

            $slots = $images->pluck('sort_order')->all();
            $validIds = $images->pluck('id')->map(static fn (mixed $id): string => (string) $id)->all();
            $orderedIds = array_values(array_intersect($imageIds, $validIds));

            foreach ($orderedIds as $index => $id) {
                Image::query()
                    ->whereKey($id)
                    ->update(['sort_order' => self::TEMPORARY_ORDER_OFFSET + $index + 1]);
            }

            foreach ($orderedIds as $index => $id) {
                Image::query()
                    ->whereKey($id)
                    ->update(['sort_order' => $slots[$index]]);
            }
        });
    }

    /**
     * @return array<int, string>
     */
    protected function orderedIds(?string $categoryId, ?string $except = null): array
    {
        return Image::query()
            ->where('image_category_id', $categoryId)
            ->when($except !== null, fn ($query) => $query->where('id', '<>', $except))
            ->orderBy('sort_order')
            ->pluck('id')
            ->map(static fn (mixed $id): string => (string) $id)
            ->all();
    }

    /**
     * @param array<int, string> $imageIds
     */
    protected function rewriteSortOrders(array $imageIds): void
    {
        foreach ($imageIds as $index => $id) {
            Image::query()
                ->whereKey($id)
                ->update(['sort_order' => self::TEMPORARY_ORDER_OFFSET + $index + 1]);
        }

        foreach ($imageIds as $index => $id) {
            Image::query()
                ->whereKey($id)
                ->update(['sort_order' => $index + 1]);
        }
    }
}
