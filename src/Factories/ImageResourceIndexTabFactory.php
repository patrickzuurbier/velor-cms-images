<?php

declare(strict_types=1);

namespace Velor\Images\Factories;

use App\Resources\Tabs\ResourceTab;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Velor\Images\Factories\Contracts\ImageResourceIndexTabFactoryInterface;
use Velor\Images\Repositories\Contracts\ImageManagementRepositoryInterface;
use Velor\Images\Resolvers\Contracts\ImageResourceScopeResolverInterface;

class ImageResourceIndexTabFactory implements ImageResourceIndexTabFactoryInterface
{
    public function __construct(
        protected UrlGenerator $urlGenerator,
        protected Translator $translator,
        protected ImageManagementRepositoryInterface $imageManagementRepository,
        protected ImageResourceScopeResolverInterface $imageResourceScopeResolver,
    ) {
    }

    public function make(Request $request): array
    {
        $tabs = [];
        $resourceTypes = $this->imageManagementRepository->resourceTypes();

        foreach ($resourceTypes as $resourceType) {
            $scope = ImageResourceScopeResolverInterface::RESOURCE_PREFIX . $resourceType;
            $tabs[] = ResourceTab::make($this->resourceLabel($resourceType))
                ->url($this->urlGenerator->route('images.index', ['scope' => $scope]))
                ->activeWhen(
                    fn (Request $request): bool => $this->imageResourceScopeResolver->scope(
                        $request,
                        $resourceTypes,
                    ) === $scope,
                )
                ->onlyOnIndex();
        }

        $tabs[] = ResourceTab::make(__('velor-images::resources.images.tabs.unused'))
            ->url($this->urlGenerator->route('images.index', [
                'scope' => ImageResourceScopeResolverInterface::UNUSED,
            ]))
            ->activeWhen(
                fn (Request $request): bool => $this->imageResourceScopeResolver->scope(
                    $request,
                    $resourceTypes,
                ) === ImageResourceScopeResolverInterface::UNUSED,
            )
            ->onlyOnIndex();

        return $tabs;
    }

    protected function resourceLabel(string $resourceType): string
    {
        $translationKey = 'resources.' . str_replace('_', '-', $resourceType) . '.plural';
        $translation = $this->translator->get($translationKey);

        return $translation === $translationKey
            ? Str::headline($resourceType)
            : $translation;
    }
}
