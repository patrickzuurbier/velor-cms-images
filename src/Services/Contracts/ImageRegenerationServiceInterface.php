<?php

declare(strict_types=1);

namespace Velor\Images\Services\Contracts;

interface ImageRegenerationServiceInterface
{
    public function regenerate(int $chunkSize = 100): int;
}
