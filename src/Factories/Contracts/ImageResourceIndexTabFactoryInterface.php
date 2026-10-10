<?php

declare(strict_types=1);

namespace Velor\Images\Factories\Contracts;

use App\Resources\Tabs\ResourceTab;
use Illuminate\Http\Request;

interface ImageResourceIndexTabFactoryInterface
{
    /**
     * @return array<int, ResourceTab>
     */
    public function make(Request $request): array;
}
