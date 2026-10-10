<?php

declare(strict_types=1);

namespace Velor\Images\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Velor\Images\Factories\Contracts\ImagePickerDataFactoryInterface;

class ImagePickerController extends Controller
{
    public function __construct(
        protected ImagePickerDataFactoryInterface $imagePickerDataFactory,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $this->authorize('viewAny', Image::class);

        return new JsonResponse([
            'images' => $this->imagePickerDataFactory->make(),
        ]);
    }
}
