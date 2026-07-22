<?php

declare(strict_types=1);

namespace Velor\Images\Http\Controllers;

use App\Http\Controllers\Controller;
use Velor\Images\Services\Contracts\ImagePickerDataFactoryInterface;
use Illuminate\Http\JsonResponse;

class ImagePickerController extends Controller
{
    public function __construct(
        protected ImagePickerDataFactoryInterface $imagePickerDataFactory,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'images' => $this->imagePickerDataFactory->make(),
        ]);
    }
}
