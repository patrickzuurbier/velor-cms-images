<?php

declare(strict_types=1);

namespace Velor\Images\Http\Controllers;

use App\Http\Controllers\Controller;
use Velor\Images\Http\Requests\ImageStoreRequest;
use Velor\Images\Http\Requests\ImageUpdateRequest;
use Velor\Images\Models\ImageCategory;
use Velor\Images\Services\Contracts\ImageOrderServiceInterface;
use Velor\Images\Services\Contracts\ImageUploadServiceInterface;
use App\Models\AbstractModel;
use Velor\Images\Models\Image;
use App\Services\Resources\Contracts\ResourceIndexQueryInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class ImageController extends Controller
{
    public function __construct(
        protected ResourceIndexQueryInterface $resourceIndexQuery,
        protected ImageUploadServiceInterface $imageUploadService,
        protected ImageOrderServiceInterface $imageOrderService,
    ) {
        $this->authorizeResource(Image::class);
    }

    public function index(Request $request): View
    {
        return view('cms.layouts.index', [
            'pagination' => $this->resourceIndexQuery->paginate(
                model: Image::class,
                search: $request->string('search')->toString(),
                filter: function (EloquentBuilder $query) use ($request): void {
                    $this->applyCategoryFilter($query, $request);

                    if (! $request->has('sort')) {
                        $query->orderBy('sort_order');
                    }
                },
            ),
            'model' => new Image(),
        ]);
    }

    public function create(): View
    {
        return view('cms.layouts.form', [
            'model' => new Image(),
        ]);
    }

    public function store(ImageStoreRequest $request): RedirectResponse
    {
        $file = $request->file('image');

        if (! $file instanceof UploadedFile) {
            return redirect()
                ->back()
                ->withErrors(['image' => 'The image field is required.'])
                ->with('error', 'Image upload failed.');
        }

        $categoryId = $request->validated('image_category_id');
        $image = $this->imageUploadService->upload(
            file: $file,
            categoryId: is_string($categoryId) ? $categoryId : null,
        );

        return redirect()
            ->route('images.show', ['image' => $image->id])
            ->with('status', 'Image uploaded.');
    }

    public function show(Image $image): View
    {
        return view('cms.layouts.show', [
            'model' => $image,
        ]);
    }

    public function edit(Image $image): View
    {
        return view('cms.layouts.form', [
            'model' => $image,
        ]);
    }

    public function update(ImageUpdateRequest $request, Image $image): RedirectResponse
    {
        $input = $request->validated();
        $categoryId = $input['image_category_id'] ?? null;
        $sortOrder = (int) ($input['sort_order'] ?? $image->sort_order);

        unset($input['image_category_id'], $input['sort_order']);

        $image->fill($input);
        $this->imageOrderService->moveToCategoryAt(
            image: $image,
            categoryId: is_string($categoryId) ? $categoryId : null,
            sortOrder: $sortOrder,
        );

        return redirect()
            ->route('images.show', ['image' => $image->id])
            ->with('status', 'Image updated.');
    }

    public function destroy(Image $image): RedirectResponse
    {
        $this->imageUploadService->delete($image);

        return redirect()
            ->route('images.index')
            ->with('status', 'Image deleted.');
    }

    /**
     * @param EloquentBuilder<AbstractModel> $query
     */
    protected function applyCategoryFilter(EloquentBuilder $query, Request $request): void
    {
        $category = $request->query('image_category');

        if ($category === 'common') {
            $query->whereNull('image_category_id');

            return;
        }

        if (! is_string($category) || $category === '') {
            return;
        }

        if (! ImageCategory::query()->whereKey($category)->exists()) {
            return;
        }

        $query->where('image_category_id', $category);
    }
}
