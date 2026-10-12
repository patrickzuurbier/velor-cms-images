<?php

declare(strict_types=1);

namespace Velor\Images\Resolvers\Contracts;

use App\Data\View\ImagePreviewData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface ImageResourceFieldValueResolverInterface
{
    public function preview(Model $model, Request $request, string $action): ?ImagePreviewData;

    /**
     * @return array<string, mixed>
     */
    public function properties(Model $model, Request $request): array;
}
