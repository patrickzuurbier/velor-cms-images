<?php

declare(strict_types=1);

namespace Velor\Images\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Repositories\Contracts\ImageRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Velor\Images\Http\Requests\ImageProcessingStatusRequest;

class ImageProcessingStatusController extends Controller
{
    public function __construct(
        protected ImageRepositoryInterface $imageRepository,
    ) {
    }

    public function __invoke(ImageProcessingStatusRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Image::class);

        $imageIds = $request->imageIds();
        $statuses = [];

        foreach ($this->imageRepository->findMany($imageIds) as $image) {
            $statuses[(string) $image->getKey()] = $image->processing_status->value;
        }

        return new JsonResponse([
            'images' => array_map(
                static fn (string $imageId): array => [
                    'id'                => $imageId,
                    'processing_status' => $statuses[$imageId] ?? 'failed',
                ],
                $imageIds,
            ),
        ]);
    }
}
