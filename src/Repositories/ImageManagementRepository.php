<?php

declare(strict_types=1);

namespace Velor\Images\Repositories;

use App\Enums\ImageProcessingStatusEnum;
use App\Models\Image;
use App\Models\ImageUsage;
use App\Repositories\AbstractRepository;
use App\Repositories\Contracts\ImageRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Velor\Images\Repositories\Contracts\ImageManagementRepositoryInterface;

/**
 * @extends AbstractRepository<Image>
 */
class ImageManagementRepository extends AbstractRepository implements ImageManagementRepositoryInterface
{
    /**
     * @var class-string<Image>
     */
    protected string $model = Image::class;

    public function __construct(
        protected ImageRepositoryInterface $imageRepository,
    ) {
    }

    public function resourceTypes(): array
    {
        return ImageUsage::query()
            ->distinct()
            ->orderBy('resource_type')
            ->pluck('resource_type')
            ->all();
    }

    public function indexQuery(?string $resourceType): Builder
    {
        $query = $this->query();

        if ($resourceType === null) {
            return $query->doesntHave('usages');
        }

        return $query->whereHas('usages', function (Builder $query) use ($resourceType): void {
            $query->where('resource_type', $resourceType);
        });
    }

    public function pickerImages(): Collection
    {
        return $this->query()
            ->where('processing_status', ImageProcessingStatusEnum::COMPLETED)
            ->orderBy('name')
            ->orderBy('filename')
            ->orderBy('id')
            ->get();
    }

    public function update(Image $image, array $attributes): Image
    {
        $image->fill($attributes);

        return $this->imageRepository->save($image);
    }

    public function isUnused(Image $image): bool
    {
        return ! $image->usages()->exists();
    }
}
