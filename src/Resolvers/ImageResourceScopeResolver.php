<?php

declare(strict_types=1);

namespace Velor\Images\Resolvers;

use Illuminate\Http\Request;
use Velor\Images\Repositories\Contracts\ImageManagementRepositoryInterface;
use Velor\Images\Resolvers\Contracts\ImageResourceScopeResolverInterface;

class ImageResourceScopeResolver implements ImageResourceScopeResolverInterface
{
    public function __construct(
        protected ImageManagementRepositoryInterface $imageManagementRepository,
    ) {
    }

    public function resourceType(Request $request): ?string
    {
        $scope = $this->scope($request);

        if (! str_starts_with($scope, ImageResourceScopeResolverInterface::RESOURCE_PREFIX)) {
            return null;
        }

        return substr($scope, strlen(ImageResourceScopeResolverInterface::RESOURCE_PREFIX));
    }

    public function scope(Request $request, ?array $resourceTypes = null): string
    {
        $scope = $request->query('scope');
        $resourceTypes ??= $this->imageManagementRepository->resourceTypes();

        if (is_string($scope) && $scope === ImageResourceScopeResolverInterface::UNUSED) {
            return $scope;
        }

        if (is_string($scope) && str_starts_with($scope, ImageResourceScopeResolverInterface::RESOURCE_PREFIX)) {
            $resourceType = substr($scope, strlen(ImageResourceScopeResolverInterface::RESOURCE_PREFIX));

            if (in_array($resourceType, $resourceTypes, true)) {
                return $scope;
            }
        }

        return $resourceTypes === []
            ? ImageResourceScopeResolverInterface::UNUSED
            : ImageResourceScopeResolverInterface::RESOURCE_PREFIX . $resourceTypes[0];
    }
}
