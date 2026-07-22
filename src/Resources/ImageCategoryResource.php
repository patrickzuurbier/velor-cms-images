<?php

declare(strict_types=1);

namespace Velor\Images\Resources;

use App\Resources\AbstractResource;
use App\Resources\Fields\Field;
use App\Resources\Fields\Text;
use App\Resources\Validation\Unique;
use Illuminate\Http\Request;
use Velor\Images\Models\ImageCategory;

class ImageCategoryResource extends AbstractResource
{
    public static string $model = ImageCategory::class;

    public function __construct(
        protected Request $request,
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
            Text::make('name')
                ->label(__('velor-images::resources.image-categories.fields.name'))
                ->sortable()
                ->searchable()
                ->rules([
                    'required',
                    'max:255',
                    Unique::make('image_categories'),
                ]),
        ];
    }
}
