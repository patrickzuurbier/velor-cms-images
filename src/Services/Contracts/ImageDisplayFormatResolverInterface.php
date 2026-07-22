<?php

declare(strict_types=1);

namespace Velor\Images\Services\Contracts;

use Velor\Images\Models\Image;

interface ImageDisplayFormatResolverInterface
{
    public function index(Image $image): string;

    public function cms(Image $image): string;
}
