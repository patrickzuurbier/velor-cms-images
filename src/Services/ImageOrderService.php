<?php

declare(strict_types=1);

namespace Velor\Images\Services;

use App\Services\Resources\Contracts\ResourceRowOrderServiceInterface;
use Illuminate\Database\ConnectionInterface;
use Velor\Images\Models\Image;
use Velor\Images\Services\Contracts\ImageOrderServiceInterface;

class ImageOrderService implements ImageOrderServiceInterface
{
    public function __construct(
        protected ConnectionInterface $database,
        protected ResourceRowOrderServiceInterface $rowOrderService,
    ) {
    }

    public function nextSortOrder(?string $categoryId): int
    {
        return (new Image([
            'image_category_id' => $categoryId,
        ]))->nextRowOrderPosition();
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
            $image->saveQuietly();

            if ($currentCategoryId !== $categoryId) {
                $this->reorderScope(
                    categoryId: is_string($currentCategoryId) ? $currentCategoryId : null,
                    imageIds: $this->orderedIds(is_string($currentCategoryId) ? $currentCategoryId : null),
                );
            }

            $this->reorderScope($categoryId, $targetIds);

            $image->refresh();
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
    protected function reorderScope(?string $categoryId, array $imageIds): void
    {
        $this->rowOrderService->reorder(
            modelClass: Image::class,
            orderColumn: (new Image())->rowOrderColumn(),
            ids: $imageIds,
            scopeColumn: 'image_category_id',
            scopeValue: $categoryId,
        );
    }
}
