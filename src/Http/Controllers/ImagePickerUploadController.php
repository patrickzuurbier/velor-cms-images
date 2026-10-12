<?php

declare(strict_types=1);

namespace Velor\Images\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Images\Contracts\ImageUploadServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Velor\Images\Factories\Contracts\ImagePickerDataFactoryInterface;
use Velor\Images\Http\Requests\ImagePickerUploadRequest;

class ImagePickerUploadController extends Controller
{
    public function __construct(
        protected ImageUploadServiceInterface $imageUploadService,
        protected ImagePickerDataFactoryInterface $imagePickerDataFactory,
    ) {
    }

    public function __invoke(ImagePickerUploadRequest $request): JsonResponse
    {
        $file = $request->file('image');

        if (! $file instanceof UploadedFile) {
            throw new RuntimeException('The image picker upload must contain one file.');
        }

        $image = $this->imageUploadService->upload($file);

        return new JsonResponse([
            'image' => $this->imagePickerDataFactory->makeFor($image),
        ], JsonResponse::HTTP_CREATED);
    }
}
