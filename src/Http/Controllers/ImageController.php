<?php

declare(strict_types=1);

namespace Velor\Images\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Services\Images\Contracts\ImageUploadServiceInterface;
use App\Services\Resources\Contracts\ResourceIndexQueryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Velor\Images\Http\Requests\ImageRequest;
use Velor\Images\Repositories\Contracts\ImageManagementRepositoryInterface;
use Velor\Images\Resources\ImageResource;

class ImageController extends Controller
{
    public function __construct(
        protected ResourceIndexQueryInterface $resourceIndexQuery,
        protected ImageUploadServiceInterface $imageUploadService,
        protected ImageManagementRepositoryInterface $imageManagementRepository,
        protected ImageResource $imageResource,
    ) {
        $this->authorizeResource(Image::class);
    }

    public function index(Request $request): View
    {
        return view('cms.layouts.index', [
            'pagination' => $this->resourceIndexQuery->paginate(
                resource: $this->imageResource,
                search: $request->string('search')->toString(),
            ),
            'resource' => $this->imageResource,
        ]);
    }

    public function create(): View
    {
        return view('cms.layouts.form', [
            'resource' => $this->imageResource,
        ]);
    }

    public function store(ImageRequest $request): RedirectResponse|JsonResponse
    {
        $image = $this->imageUploadService->upload($this->uploadedFile($request));

        if ($request->expectsJson()) {
            return new JsonResponse([
                'image' => [
                    'id'                => (string) $image->getKey(),
                    'processing_status' => $image->processing_status->value,
                ],
            ], JsonResponse::HTTP_CREATED);
        }

        return redirect()
            ->route('images.show', ['image' => $image->getKey()])
            ->with('status', __('velor-images::resources.images.messages.uploaded'));
    }

    public function show(Image $image): View
    {
        return view('cms.layouts.show', [
            'resource' => $this->imageResource,
        ]);
    }

    public function edit(Image $image): View
    {
        return view('cms.layouts.form', [
            'resource' => $this->imageResource,
        ]);
    }

    public function update(ImageRequest $request, Image $image): RedirectResponse
    {
        $name = $request->validated('name');

        if (! is_string($name)) {
            throw new RuntimeException('The image name must be a string.');
        }

        $this->imageManagementRepository->update($image, ['name' => $name]);

        return redirect()
            ->route('images.show', ['image' => $image->getKey()])
            ->with('status', __('velor-images::resources.images.messages.updated'));
    }

    public function destroy(Image $image): RedirectResponse
    {
        $this->imageUploadService->delete($image);

        return redirect()
            ->route('images.index', ['scope' => 'unused'])
            ->with('status', __('velor-images::resources.images.messages.deleted'));
    }

    protected function uploadedFile(ImageRequest $request): UploadedFile
    {
        $uploads = $request->validated('upload.uploads');
        $file = is_array($uploads) ? ($uploads[0] ?? null) : null;

        if (! $file instanceof UploadedFile) {
            throw new RuntimeException('The image upload must contain one file.');
        }

        return $file;
    }
}
