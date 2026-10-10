<?php

declare(strict_types=1);

namespace Velor\Images\Resolvers\Contracts;

use Illuminate\Http\Request;

interface ImageResourceScopeResolverInterface
{
    public const UNUSED = 'unused';

    public const RESOURCE_PREFIX = 'resource:';

    public function resourceType(Request $request): ?string;

    /**
     * @param array<int, string>|null $resourceTypes
     */
    public function scope(Request $request, ?array $resourceTypes = null): string;
}
