<?php

declare(strict_types=1);

namespace Velor\Images\Services\Contracts;

use Velor\Images\Models\Image;

interface ImageUrlGeneratorInterface
{
    public function original(Image $image): string;

    public function format(Image $image, string $format): string;
}
