<?php

declare(strict_types=1);

namespace Velor\Images\Factories\Contracts;

use App\Models\Image;
use App\Resources\Tabs\ResourceTab;
use Illuminate\Http\Request;

interface ImageFormatPresentationFactoryInterface
{
    /**
     * @return array<string, array{
     *     label: string,
     *     url: string,
     *     extension: string,
     *     mime_type: string,
     *     size: int,
     *     size_label: string,
     *     width: int,
     *     height: int,
     * }>
     */
    public function selected(Image $image, Request $request): array;

    /**
     * @return array<int, ResourceTab>
     */
    public function tabs(Request $request): array;
}
