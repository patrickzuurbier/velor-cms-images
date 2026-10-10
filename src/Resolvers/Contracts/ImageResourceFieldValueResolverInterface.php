<?php

declare(strict_types=1);

namespace Velor\Images\Resolvers\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ImageResourceFieldValueResolverInterface
{
    public function previewUrl(Model $model): ?string;

    /**
     * @return array<string, mixed>
     */
    public function originalMetaData(Model $model): array;
}
