<?php

declare(strict_types=1);

namespace Velor\Images\Services;

use Velor\Images\Models\Image;
use Velor\Images\Services\Contracts\ImagePickerDataFactoryInterface;
use Velor\Images\Services\Contracts\ImageUrlGeneratorInterface;
use Illuminate\Contracts\Config\Repository;

class ImagePickerDataFactory implements ImagePickerDataFactoryInterface
{
    public function __construct(
        protected ImageUrlGeneratorInterface $imageUrlGenerator,
        protected Repository $config,
    ) {
    }

    /**
     * @return array<int, array{
     *     id: string,
     *     name: mixed,
     *     description: mixed,
     *     alt: mixed,
     *     thumbnail_url: string,
     *     insert_url: string,
     *     insert_format: string,
     *     formats: array<string, string>,
     * }>
     */
    public function make(): array
    {
        return Image::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Image $image): array => $this->imageData($image))
            ->all();
    }

    /**
     * @return array{
     *     id: string,
     *     name: mixed,
     *     description: mixed,
     *     alt: mixed,
     *     thumbnail_url: string,
     *     insert_url: string,
     *     insert_format: string,
     *     formats: array<string, string>,
     * }
     */
    protected function imageData(Image $image): array
    {
        $defaultFormat = $this->defaultFormat();

        return [
            'id'            => (string) $image->getKey(),
            'name'          => $image->name,
            'description'   => $image->description,
            'alt'           => $image->description ?: $image->name,
            'thumbnail_url' => $this->imageUrlGenerator->format($image, 'thumbnail'),
            'insert_url'    => $this->imageUrlGenerator->format($image, $defaultFormat),
            'insert_format' => $defaultFormat,
            'formats'       => $this->formats($image),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function formats(Image $image): array
    {
        $formats = [];

        foreach ($this->pickerFormats() as $format) {
            $formats[$format] = $this->imageUrlGenerator->format($image, $format);
        }

        return $formats;
    }

    /**
     * @return array<int, string>
     */
    protected function pickerFormats(): array
    {
        $formats = $this->configuredPickerFormats();
        $availableFormats = $this->availableFormats();

        if ($formats === []) {
            return array_values(array_filter(
                $availableFormats,
                fn (string $format): bool => $format !== 'cms',
            ));
        }

        return array_values(array_intersect($formats, $availableFormats));
    }

    protected function defaultFormat(): string
    {
        $format = $this->config->get('velor-images.picker.default_format');
        $formats = $this->pickerFormats();

        if (is_string($format) && in_array($format, $formats, true)) {
            return $format;
        }

        return $formats[0] ?? '';
    }

    /**
     * @return array<int, string>
     */
    protected function configuredPickerFormats(): array
    {
        $formats = $this->config->get('velor-images.picker.formats', []);

        return is_array($formats)
            ? array_values(array_filter($formats, 'is_string'))
            : [];
    }

    /**
     * @return array<int, string>
     */
    protected function availableFormats(): array
    {
        $formats = $this->config->get('velor-images.formats', []);

        return is_array($formats)
            ? array_values(array_filter(array_keys($formats), 'is_string'))
            : [];
    }
}
