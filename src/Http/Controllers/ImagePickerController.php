<?php

declare(strict_types=1);

namespace Velor\Images\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Http\JsonResponse;
use Velor\Images\Factories\Contracts\ImagePickerDataFactoryInterface;

class ImagePickerController extends Controller
{
    public function __construct(
        protected ImagePickerDataFactoryInterface $imagePickerDataFactory,
        protected Translator $translator,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $this->authorize('viewAny', Image::class);

        return new JsonResponse([
            'images' => $this->imagePickerDataFactory->make(),
            'labels' => [
                'alt'           => $this->translator->get('velor-images::resources.images.picker.alt'),
                'close'         => $this->translator->get('velor-images::resources.images.picker.close'),
                'empty'         => $this->translator->get('velor-images::resources.images.picker.empty'),
                'format'        => $this->translator->get('velor-images::resources.images.picker.format'),
                'no_formats'    => $this->translator->get('velor-images::resources.images.picker.no_formats'),
                'open'          => $this->translator->get('velor-images::resources.images.picker.open'),
                'search'        => $this->translator->get('velor-images::resources.images.picker.search'),
                'select'        => $this->translator->get('velor-images::resources.images.picker.select'),
                'title'         => $this->translator->get('velor-images::resources.images.picker.title'),
                'upload'        => $this->translator->get('velor-images::resources.images.picker.upload'),
                'upload_failed' => $this->translator->get('velor-images::resources.images.picker.upload_failed'),
                'uploading'     => $this->translator->get('velor-images::resources.images.picker.uploading'),
            ],
        ]);
    }
}
