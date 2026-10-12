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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Velor\Images\Factories\Contracts\ImageFormatPresentationFactoryInterface;
use Velor\Images\Factories\Contracts\ImageResourceIndexTabFactoryInterface;
use Velor\Images\Repositories\Contracts\ImageManagementRepositoryInterface;
use Velor\Images\Resolvers\Contracts\ImageResourceFieldValueResolverInterface;
use Velor\Images\Resolvers\Contracts\ImageResourceScopeResolverInterface;

class ImageResource extends AbstractResource
{
    public static string $model = Image::class;

    public function __construct(
        protected Request $request,
        protected ImageManagementRepositoryInterface $imageManagementRepository,
        protected ImageFormatPresentationFactoryInterface $imageFormatPresentationFactory,
        protected ImageResourceIndexTabFactoryInterface $imageResourceIndexTabFactory,
        protected ImageResourceFieldValueResolverInterface $imageResourceFieldValueResolver,
        protected ImageResourceScopeResolverInterface $imageResourceScopeResolver,
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
        $query = $this->imageManagementRepository->indexQuery(
            $this->imageResourceScopeResolver->resourceType($this->request),
        );

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
                ->resolveValueUsing(
                    fn (Model $model, Field $_field, string $action): mixed => $this->imageResourceFieldValueResolver->preview(
                        $model,
                        $this->request,
                        $action,
                    ),
                )
                ->exceptOnForms(),
            ImageUpload::make('upload')
                ->label(__('velor-images::resources.images.fields.image'))
                ->imagePicker(false)
                ->withoutUsageContext()
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
            ImageMetaData::make('properties')
                ->label(__('velor-images::resources.images.fields.properties'))
                ->resolveValueUsing(
                    fn (Model $model): array => $this->imageResourceFieldValueResolver->properties(
                        $model,
                        $this->request,
                    ),
                )
                ->onlyOnShow(),
        ];
    }

    /**
     * @return array<int, ResourceTab>
     */
    public function tabs(): array
    {
        $tabs = $this->imageResourceIndexTabFactory->make($this->request);

        array_push($tabs, ...$this->imageFormatPresentationFactory->tabs($this->request));

        return $tabs;
    }
}
