<?php

declare(strict_types=1);

namespace Velor\Images\Resources;

use App\Models\AbstractModel;
use App\Models\Image;
use App\Resources\AbstractResource;
use App\Resources\Fields\Field;
use App\Resources\Fields\ImageMetaData;
use App\Resources\Fields\ImagePreview;
use App\Resources\Fields\ImageUpload;
use App\Resources\Fields\Text;
use App\Resources\Tabs\ResourceTab;
use App\Services\Images\Contracts\ImageUrlGeneratorInterface;
use App\Services\Images\Resolvers\ImageDisplayFormatResolver;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Velor\Images\Repositories\Contracts\ImageManagementRepositoryInterface;

class ImageResource extends AbstractResource
{
    public static string $model = Image::class;

    protected const UNUSED_SCOPE = 'unused';

    protected const RESOURCE_SCOPE_PREFIX = 'resource:';

    public function __construct(
        protected Request $request,
        protected UrlGenerator $urlGenerator,
        protected Translator $translator,
        protected ImageUrlGeneratorInterface $imageUrlGenerator,
        protected ImageDisplayFormatResolver $imageDisplayFormatResolver,
        protected ImageManagementRepositoryInterface $imageManagementRepository,
    ) {
    }

    public function titleAttribute(): string
    {
        return 'name';
    }

    /**
     * @return Builder<AbstractModel>
     */
    public function indexQuery(?AbstractModel $parent = null, ?string $relationship = null): Builder
    {
        /** @var Builder<AbstractModel> $query */
        $query = $this->imageManagementRepository->indexQuery($this->selectedResourceType());

        return $query;
    }

    /**
     * @return array<int, Field>
     */
    public function fields(): array
    {
        return [
            ImagePreview::make('preview')
                ->label(__('velor-images::resources.images.fields.image'))
                ->resolveValueUsing(fn (Model $model): ?string => $this->imageUrl($model))
                ->exceptOnForms(),
            ImageUpload::make('upload')
                ->label(__('velor-images::resources.images.fields.image'))
                ->onlyOnCreate()
                ->rules(['required']),
            Text::make('name')
                ->label(__('velor-images::resources.images.fields.name'))
                ->sortable()
                ->searchable()
                ->hideOnCreate()
                ->rules(['required', 'max:255']),
            Text::make('filename')
                ->label(__('velor-images::resources.images.fields.filename'))
                ->sortable()
                ->searchable()
                ->exceptOnForms(),
            ImageMetaData::make('original_meta_data')
                ->label(__('velor-images::resources.images.fields.original_metadata'))
                ->resolveValueUsing(fn (Model $model): array => $this->originalMetaData($model))
                ->onlyOnShow(),
        ];
    }

    /**
     * @return array<int, ResourceTab>
     */
    public function tabs(): array
    {
        $tabs = [];
        $resourceTypes = $this->imageManagementRepository->resourceTypes();

        foreach ($resourceTypes as $resourceType) {
            $scope = self::RESOURCE_SCOPE_PREFIX . $resourceType;
            $tabs[] = ResourceTab::make($this->resourceLabel($resourceType))
                ->url($this->urlGenerator->route('images.index', ['scope' => $scope]))
                ->activeWhen(fn (Request $request): bool => $this->scope($request, $resourceTypes) === $scope)
                ->onlyOnIndex();
        }

        $tabs[] = ResourceTab::make(__('velor-images::resources.images.tabs.unused'))
            ->url($this->urlGenerator->route('images.index', ['scope' => self::UNUSED_SCOPE]))
            ->activeWhen(
                fn (Request $request): bool => $this->scope($request, $resourceTypes) === self::UNUSED_SCOPE
            )
            ->onlyOnIndex();

        return $tabs;
    }

    protected function selectedResourceType(): ?string
    {
        $scope = $this->scope($this->request);

        if (! str_starts_with($scope, self::RESOURCE_SCOPE_PREFIX)) {
            return null;
        }

        return substr($scope, strlen(self::RESOURCE_SCOPE_PREFIX));
    }

    /**
     * @param Request $request
     * @param array<int, string>|null $resourceTypes
     */
    protected function scope(Request $request, ?array $resourceTypes = null): string
    {
        $scope = $request->query('scope');
        $resourceTypes ??= $this->imageManagementRepository->resourceTypes();

        if (is_string($scope) && $scope === self::UNUSED_SCOPE) {
            return $scope;
        }

        if (is_string($scope) && str_starts_with($scope, self::RESOURCE_SCOPE_PREFIX)) {
            $resourceType = substr($scope, strlen(self::RESOURCE_SCOPE_PREFIX));

            if (in_array($resourceType, $resourceTypes, true)) {
                return $scope;
            }
        }

        return $resourceTypes === []
            ? self::UNUSED_SCOPE
            : self::RESOURCE_SCOPE_PREFIX . $resourceTypes[0];
    }

    protected function resourceLabel(string $resourceType): string
    {
        $translationKey = 'resources.' . str_replace('_', '-', $resourceType) . '.plural';
        $translation = $this->translator->get($translationKey);

        return $translation === $translationKey
            ? Str::headline($resourceType)
            : $translation;
    }

    protected function imageUrl(Model $model): ?string
    {
        if (! $model instanceof Image) {
            return null;
        }

        return $this->imageUrlGenerator->format(
            $model,
            $this->imageDisplayFormatResolver->cms($model),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function originalMetaData(Model $model): array
    {
        if (! $model instanceof Image) {
            return [];
        }

        $metaData = $model->metaData();
        $original = $metaData['original'] ?? [];

        return is_array($original) ? $original : [];
    }
}
