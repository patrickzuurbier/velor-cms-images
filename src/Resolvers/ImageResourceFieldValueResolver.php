<?php

declare(strict_types=1);

namespace Velor\Images\Resolvers;

use App\Models\Image;
use App\Services\Images\Contracts\ImageUrlGeneratorInterface;
use App\Services\Images\Resolvers\ImageDisplayFormatResolver;
use Illuminate\Database\Eloquent\Model;
use Velor\Images\Resolvers\Contracts\ImageResourceFieldValueResolverInterface;

class ImageResourceFieldValueResolver implements ImageResourceFieldValueResolverInterface
{
    public function __construct(
        protected ImageUrlGeneratorInterface $imageUrlGenerator,
        protected ImageDisplayFormatResolver $imageDisplayFormatResolver,
    ) {
    }

    public function previewUrl(Model $model): ?string
    {
        if (! $model instanceof Image) {
            return null;
        }

        return $this->imageUrlGenerator->format(
            $model,
            $this->imageDisplayFormatResolver->cms($model),
        );
    }

    public function originalMetaData(Model $model): array
    {
        if (! $model instanceof Image) {
            return [];
        }

        $original = $model->metaData()['original'] ?? [];

        return is_array($original) ? $original : [];
    }
}
