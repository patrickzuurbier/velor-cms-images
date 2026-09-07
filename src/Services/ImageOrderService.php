<?php

declare(strict_types=1);

namespace Velor\Images\Services;

use App\Services\Resources\Contracts\ResourceRowOrderServiceInterface;
use Illuminate\Database\ConnectionInterface;
use Velor\Images\Models\Image;
use Velor\Images\Repositories\Contracts\ImageRepositoryInterface;
use Velor\Images\Services\Contracts\ImageOrderServiceInterface;

class ImageOrderService implements ImageOrderServiceInterface
{
    public function __construct(
        protected ConnectionInterface $database,
        protected ResourceRowOrderServiceInterface $rowOrderService,
        protected ImageRepositoryInterface $imageRepository,
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
            $targetIds = $this->imageRepository->orderedIdsForCategory($categoryId, except: (string) $image->getKey());
            $targetOrder = max(1, min($sortOrder, count($targetIds) + 1));

            array_splice($targetIds, $targetOrder - 1, 0, [(string) $image->getKey()]);

            $image->setAttribute('image_category_id', $categoryId);
            $image->setAttribute('sort_order', 0);
            $image->saveQuietly();

            if ($currentCategoryId !== $categoryId) {
                $this->reorderScope(
                    categoryId: is_string($currentCategoryId) ? $currentCategoryId : null,
                    imageIds: $this->imageRepository->orderedIdsForCategory(
                        is_string($currentCategoryId) ? $currentCategoryId : null,
                    ),
                );
            }

            $this->reorderScope($categoryId, $targetIds);

            $image->refresh();
        });
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
