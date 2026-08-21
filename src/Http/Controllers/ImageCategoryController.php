<?php

declare(strict_types=1);

namespace Velor\Images\Http\Controllers;

use App\Http\Controllers\Controller;
use Velor\Images\Http\Requests\ImageCategoryRequest;
use Velor\Images\Models\ImageCategory;
use Velor\Images\Services\Contracts\ImageOrderServiceInterface;
use App\Services\Resources\Contracts\ResourceIndexQueryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImageCategoryController extends Controller
{
    public function __construct(
        protected ResourceIndexQueryInterface $resourceIndexQuery,
        protected ImageOrderServiceInterface $imageOrderService,
    ) {
        $this->authorizeResource(ImageCategory::class, 'image_category');
    }

    public function index(Request $request): View
    {
        return view('cms.layouts.index', [
            'pagination' => $this->resourceIndexQuery->paginate(
                model: ImageCategory::class,
                search: $request->string('search')->toString(),
            ),
            'model' => new ImageCategory(),
        ]);
    }

    public function create(): View
    {
        return view('cms.layouts.form', [
            'model' => new ImageCategory(),
        ]);
    }

    public function store(ImageCategoryRequest $request): RedirectResponse
    {
        $imageCategory = ImageCategory::create($request->validated());

        return redirect()
            ->route('image-categories.show', ['image_category' => $imageCategory->id])
            ->with('status', 'Image category created.');
    }

    public function show(ImageCategory $imageCategory): View
    {
        return view('cms.layouts.show', [
            'model' => $imageCategory,
        ]);
    }

    public function edit(ImageCategory $imageCategory): View
    {
        return view('cms.layouts.form', [
            'model' => $imageCategory,
        ]);
    }

    public function update(ImageCategoryRequest $request, ImageCategory $imageCategory): RedirectResponse
    {
        $imageCategory->update($request->validated());

        return redirect()
            ->route('image-categories.show', ['image_category' => $imageCategory->id])
            ->with('status', 'Image category updated.');
    }

    public function destroy(ImageCategory $imageCategory): RedirectResponse
    {
        foreach ($imageCategory->images()->orderBy('sort_order')->get() as $image) {
            $this->imageOrderService->moveToCategory($image, null);
        }

        $imageCategory->delete();

        return redirect()
            ->route('image-categories.index')
            ->with('status', 'Image category deleted.');
    }
}
