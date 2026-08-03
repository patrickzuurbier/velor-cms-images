<?php

declare(strict_types=1);

namespace Velor\Images\Resources;

use App\Resources\AbstractResource;
use App\Data\View\ResourceRowOrderingData;
use App\Resources\Fields\Field;
use App\Resources\Fields\ImageFormats;
use App\Resources\Fields\ImageMetaData;
use App\Resources\Fields\ImagePreview;
use App\Resources\Fields\ImageUpload;
use App\Resources\Fields\Number;
use App\Resources\Fields\Select;
use App\Resources\Fields\Text;
use App\Resources\Fields\Textarea;
use App\Resources\Tabs\ResourceTab;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Velor\Images\Models\Image;
use Velor\Images\Models\ImageCategory;
use Velor\Images\Services\Contracts\ImageDisplayFormatResolverInterface;
use Velor\Images\Services\Contracts\ImageFormatDataFactoryInterface;
use Velor\Images\Services\Contracts\ImageUrlGeneratorInterface;

class ImageResource extends AbstractResource
{
    public static string $model = Image::class;

    public function __construct(
        protected Repository $config,
        protected UrlGenerator $urlGenerator,
        protected ImageUrlGeneratorInterface $imageUrlGenerator,
        protected ImageDisplayFormatResolverInterface $imageDisplayFormatResolver,
        protected ImageFormatDataFactoryInterface $imageFormatDataFactory,
    ) {
    }

    public function titleAttribute(): string
    {
        return 'name';
    }

    /**
     * @return array<int, Field>
     */
    public function fields(): array
    {
        return [
            ImagePreview::make('name')
                ->label(__('velor-images::resources.images.fields.image'))
                ->resolveValueUsing(fn (Model $model, Field $_field, string $action): ?string => $this->imageUrl($model, $action))
                ->hideOnCreate(),
            ImageMetaData::make('original_meta_data')
                ->label(__('velor-images::resources.images.fields.original_metadata'))
                ->onlyOnShow(),
            ImageFormats::make('image_formats')
                ->label(__('velor-images::resources.images.fields.formats'))
                ->resolveValueUsing(fn (Model $model, Field $_field, string $_action): array => $this->imageFormats($model))
                ->onlyOnShow(),
            ImageUpload::make('image')
                ->label(__('velor-images::resources.images.fields.image'))
                ->onlyOnCreate()
                ->rules([
                    'required',
                    'mimes:' . implode(',', $this->mimes()),
                    'max:' . $this->maxSize(),
                ]),
            Text::make('name')
                ->label(__('velor-images::resources.images.fields.name'))
                ->sortable()
                ->searchable()
                ->hideOnCreate()
                ->rules([
                    'required',
                    'max:255',
                ]),
            Text::make('filename')
                ->label(__('velor-images::resources.images.fields.filename'))
                ->sortable()
                ->searchable()
                ->exceptOnForms(),
            Text::make('category_name')
                ->label(__('velor-images::resources.images.fields.category'))
                ->exceptOnForms(),
            Number::make('sort_order')
                ->label(__('velor-images::resources.images.fields.order'))
                ->sortable()
                ->hideOnCreate()
                ->rules([
                    'required',
                    'integer',
                    'min:1',
                ]),
            Select::make('image_category_id')
                ->label(__('velor-images::resources.images.fields.category'))
                ->options(fn (): array => $this->categories())
                ->defaultFromQuery('image_category')
                ->onlyOnForms()
                ->rules([
                    'nullable',
                    Rule::exists('image_categories', 'id'),
                ]),
            Textarea::make('description')
                ->label(__('velor-images::resources.images.fields.description'))
                ->help(__('velor-images::resources.images.help.description'))
                ->translatable()
                ->hideFromIndex()
                ->hideOnCreate()
                ->rules([
                    'nullable',
                ]),
        ];
    }

    /**
     * @return array<int, ResourceTab>
     */
    public function tabs(): array
    {
        $tabs = [
            ResourceTab::make(__('velor-images::resources.images.tabs.all'))
                ->url($this->urlGenerator->route('images.index'))
                ->activeWhen(fn (Request $request): bool => $request->query('image_category') === null)
                ->onlyOnIndex(),
            ResourceTab::make(__('velor-images::resources.images.tabs.common'))
                ->url($this->urlGenerator->route('images.index', ['image_category' => 'common']))
                ->activeWhen(fn (Request $request): bool => $request->query('image_category') === 'common')
                ->onlyOnIndex(),
        ];

        foreach ($this->orderedCategories() as $category) {
            $tabs[] = ResourceTab::make($category->name)
                ->url($this->urlGenerator->route('images.index', ['image_category' => $category->id]))
                ->activeWhen(
                    fn (Request $request): bool => $request->query('image_category') === $category->id
                )
                ->onlyOnIndex();
        }

        return $tabs;
    }

    /**
     * @return array<string, mixed>
     */
    public function createQueryParameters(Request $request): array
    {
        $category = $request->query('image_category');

        if (! is_string($category) || $category === 'common') {
            return [];
        }

        return ImageCategory::query()->whereKey($category)->exists()
            ? ['image_category' => $category]
            : [];
    }

    public function rowOrdering(Request $request): ?ResourceRowOrderingData
    {
        $category = $request->query('image_category');

        if ($category === null || $request->query('search') !== null || $request->query('sort') !== null) {
            return null;
        }

        return new ResourceRowOrderingData(
            url: $this->urlGenerator->route('resource-row-order.update', ['resource' => (new Image())->getTable()]),
            itemsKey: 'images',
            contextKey: 'image_category_id',
            contextValue: is_string($category) ? $category : null,
        );
    }

    /**
     * @return array<int, string>
     */
    protected function mimes(): array
    {
        $mimes = $this->config->get('velor-images.mimes', []);

        return is_array($mimes)
            ? array_values(array_filter($mimes, 'is_string'))
            : [];
    }

    protected function maxSize(): int
    {
        return (int) $this->config->get('velor-images.max_size', 10240);
    }

    /**
     * @return array<string, string>
     */
    protected function categories(): array
    {
        $options = [];

        foreach ($this->orderedCategories() as $category) {
            $options[$category->name] = (string) $category->getKey();
        }

        return $options;
    }

    /**
     * @return array<int, ImageCategory>
     */
    protected function orderedCategories(): array
    {
        return ImageCategory::query()
            ->orderBy('name')
            ->get()
            ->all();
    }

    protected function imageUrl(Model $model, string $action): ?string
    {
        if (! $model instanceof Image) {
            return null;
        }

        $format = $action === 'index'
            ? $this->imageDisplayFormatResolver->index($model)
            : $this->imageDisplayFormatResolver->cms($model);

        return $this->imageUrlGenerator->format($model, $format);
    }

    /**
     * @return array<string, mixed>
     */
    protected function imageFormats(Model $model): array
    {
        if (! $model instanceof Image) {
            return [];
        }

        return $this->imageFormatDataFactory->make($model);
    }
}
