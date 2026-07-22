<?php

declare(strict_types=1);

namespace Velor\Images\Services\Contracts;

use Velor\Images\Models\Image;

interface ImageFormatDataFactoryInterface
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
 *     height: int
 * }>
     */
    public function make(Image $image): array;
}
