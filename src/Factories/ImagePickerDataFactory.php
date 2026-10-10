<?php

declare(strict_types=1);

namespace Velor\Images\Factories;

use App\Models\Image;
use App\Services\Images\Contracts\ImageUrlGeneratorInterface;
use App\Services\Images\Resolvers\ImageDisplayFormatResolver;
use Velor\Images\Factories\Contracts\ImageFormatDataFactoryInterface;
use Velor\Images\Factories\Contracts\ImagePickerDataFactoryInterface;
use Velor\Images\Repositories\Contracts\ImageManagementRepositoryInterface;

class ImagePickerDataFactory implements ImagePickerDataFactoryInterface
{
    public function __construct(
        protected ImageManagementRepositoryInterface $imageManagementRepository,
        protected ImageUrlGeneratorInterface $imageUrlGenerator,
        protected ImageDisplayFormatResolver $imageDisplayFormatResolver,
        protected ImageFormatDataFactoryInterface $imageFormatDataFactory,
    ) {
    }

    public function make(): array
    {
        return $this->imageManagementRepository
            ->pickerImages()
            ->map(fn (Image $image): array => [
                'id'          => (string) $image->getKey(),
                'name'        => $image->name,
                'filename'    => $image->filename,
                'preview_url' => $this->imageUrlGenerator->format(
                    $image,
                    $this->imageDisplayFormatResolver->cms($image),
                ),
                'formats' => $this->formats($image),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected function formats(Image $image): array
    {
        $urls = [];

        foreach ($this->imageFormatDataFactory->make($image) as $format => $data) {
            if ($format === 'cms') {
                continue;
            }

            $urls[$format] = $data['url'];
        }

        return $urls;
    }
}
