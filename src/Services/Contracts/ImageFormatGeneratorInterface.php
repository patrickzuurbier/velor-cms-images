<?php

declare(strict_types=1);

namespace Velor\Images\Services\Contracts;

use Velor\Images\Models\Image;
use Illuminate\Http\UploadedFile;

interface ImageFormatGeneratorInterface
{
    public function generate(Image $image, UploadedFile $file): void;

    public function regenerate(Image $image): void;
}
