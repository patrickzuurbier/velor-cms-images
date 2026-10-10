<?php

declare(strict_types=1);

namespace Velor\Images\Factories;

use App\Models\Image;
use App\Resources\Tabs\ResourceTab;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;
use Velor\Images\Factories\Contracts\ImageFormatDataFactoryInterface;
use Velor\Images\Factories\Contracts\ImageFormatPresentationFactoryInterface;

class ImageFormatPresentationFactory implements ImageFormatPresentationFactoryInterface
{
    public function __construct(
        protected UrlGenerator $urlGenerator,
        protected ImageFormatDataFactoryInterface $imageFormatDataFactory,
    ) {
    }

    public function selected(Image $image, Request $request): array
    {
        $formats = $this->imageFormatDataFactory->make($image);
        $key = $this->selectedKey($request, $formats);

        return $key === null ? [] : [$key => $formats[$key]];
    }

    public function tabs(Request $request): array
    {
        $image = $this->routeImage($request);

        if (! $image instanceof Image) {
            return [];
        }

        $formats = $this->imageFormatDataFactory->make($image);
        $tabs = [];

        foreach ($formats as $key => $format) {
            $tabs[] = ResourceTab::make($format['label'])
                ->url(fn (Image $image): string => $this->urlGenerator->route('images.show', [
                    'image'  => $image->getKey(),
                    'format' => $key,
                ]))
                ->activeWhen(fn (Request $request): bool => $this->selectedKey($request, $formats) === $key)
                ->onlyOnShow();
        }

        return $tabs;
    }

    /**
     * @param array<string, mixed> $formats
     */
    protected function selectedKey(Request $request, array $formats): ?string
    {
        $selected = $request->query('format');

        return is_string($selected) && array_key_exists($selected, $formats)
            ? $selected
            : array_key_first($formats);
    }

    protected function routeImage(Request $request): ?Image
    {
        $image = $request->route('image');

        return $image instanceof Image ? $image : null;
    }
}
