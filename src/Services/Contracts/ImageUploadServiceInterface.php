<?php

declare(strict_types=1);

namespace Velor\Images\Services\Contracts;

use Velor\Images\Models\Image;
use Illuminate\Http\UploadedFile;

interface ImageUploadServiceInterface
{
    public function upload(UploadedFile $file, ?string $categoryId = null): Image;

    public function delete(Image $image): void;
}
