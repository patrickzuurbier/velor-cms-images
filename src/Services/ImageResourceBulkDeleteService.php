<?php

declare(strict_types=1);

namespace Velor\Images\Services;

use App\Models\Image;
use App\Models\User;
use App\Repositories\Contracts\ImageRepositoryInterface;
use App\Services\Images\Contracts\ImageUploadServiceInterface;
use App\Services\Resources\Contracts\ResourceBulkDeleteServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageResourceBulkDeleteService implements ResourceBulkDeleteServiceInterface
{
    public function __construct(
        protected Request $request,
        protected ResourceBulkDeleteServiceInterface $resourceBulkDeleteService,
        protected ImageUploadServiceInterface $imageUploadService,
        protected ImageRepositoryInterface $imageRepository,
    ) {
    }

    /**
     * @param class-string<Model> $modelClass
     * @param array<int, string> $ids
     */
    public function delete(string $modelClass, array $ids): int
    {
        if ($modelClass !== Image::class) {
            return $this->resourceBulkDeleteService->delete($modelClass, $ids);
        }

        $ids = array_values(array_unique($ids));

        if ($ids === []) {
            return 0;
        }

        $images = $this->imageRepository->findMany($ids);

        if ($images->count() !== count($ids)) {
            throw new NotFoundHttpException();
        }

        $user = $this->request->user();

        foreach ($images as $image) {
            if (! $user instanceof User || $user->cannot('delete', $image)) {
                throw new AuthorizationException();
            }
        }

        foreach ($images as $image) {
            $this->imageUploadService->delete($image);
        }

        return $images->count();
    }
}
