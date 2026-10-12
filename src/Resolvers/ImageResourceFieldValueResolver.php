<?php

declare(strict_types=1);

namespace Velor\Images\Resolvers;

use App\Data\View\ImagePreviewData;
use App\Models\Image;
use App\Services\Images\Contracts\ImageUrlGeneratorInterface;
use App\Services\Images\Resolvers\ImageDisplayFormatResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Velor\Images\Factories\Contracts\ImageFormatPresentationFactoryInterface;
use Velor\Images\Resolvers\Contracts\ImageResourceFieldValueResolverInterface;

class ImageResourceFieldValueResolver implements ImageResourceFieldValueResolverInterface
{
    public function __construct(
        protected ImageFormatPresentationFactoryInterface $imageFormatPresentationFactory,
        protected ImageUrlGeneratorInterface $imageUrlGenerator,
        protected ImageDisplayFormatResolver $imageDisplayFormatResolver,
    ) {
    }

    public function preview(Model $model, Request $request, string $action): ?ImagePreviewData
    {
        if (! $model instanceof Image) {
            return null;
        }

        if ($action === 'index') {
            return new ImagePreviewData($this->imageUrlGenerator->format(
                $model,
                $this->imageDisplayFormatResolver->cms($model),
            ));
        }

        $format = $this->imageFormatPresentationFactory->selected($model, $request);

        if ($format === null) {
            return null;
        }

        return new ImagePreviewData(
            url: $format['url'],
            copyUrl: $format['url'],
            width: $format['width'],
            height: $format['height'],
        );
    }

    public function properties(Model $model, Request $request): array
    {
        if (! $model instanceof Image) {
            return [];
        }

        $format = $this->imageFormatPresentationFactory->selected($model, $request);

        if ($format === null) {
            return [];
        }

        return [
            'width'     => $format['width'],
            'height'    => $format['height'],
            'mime_type' => $format['mime_type'],
            'extension' => $format['extension'],
            'size'      => $format['size'],
        ];
    }
}
