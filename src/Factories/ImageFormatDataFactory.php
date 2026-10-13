<?php

declare(strict_types=1);

namespace Velor\Images\Factories;

use App\Models\Image;
use App\Services\Images\Contracts\ImageUrlGeneratorInterface;
use Illuminate\Support\Str;
use Velor\Images\Factories\Contracts\ImageFormatDataFactoryInterface;

class ImageFormatDataFactory implements ImageFormatDataFactoryInterface
{
    protected const HIDDEN_FORMATS = ['preview', 'thumbnail'];

    public function __construct(
        protected ImageUrlGeneratorInterface $imageUrlGenerator,
    ) {
    }

    public function make(Image $image): array
    {
        $generatedFormats = $image->metaData()['formats'] ?? [];

        if (! is_array($generatedFormats)) {
            return [];
        }

        $formats = [];

        foreach ($generatedFormats as $key => $metaData) {
            if (! is_string($key) || ! is_array($metaData) || in_array($key, self::HIDDEN_FORMATS, true)) {
                continue;
            }

            $formats[$key] = $this->formatData($image, $key, $metaData);
        }

        return $formats;
    }

    /**
     * @param array<string, mixed> $metaData
     * @return array{
     *     label: string,
     *     url: string,
     *     extension: string,
     *     mime_type: string,
     *     size: int,
     *     size_label: string,
     *     width: int,
     *     height: int,
     * }
     */
    protected function formatData(Image $image, string $key, array $metaData): array
    {
        $size = (int) ($metaData['size'] ?? 0);

        return [
            'label'      => Str::headline($key),
            'url'        => $this->imageUrlGenerator->format($image, $key),
            'extension'  => (string) ($metaData['extension'] ?? ''),
            'mime_type'  => (string) ($metaData['mime_type'] ?? ''),
            'size'       => $size,
            'size_label' => $this->fileSize($size),
            'width'      => (int) ($metaData['width'] ?? 0),
            'height'     => (int) ($metaData['height'] ?? 0),
        ];
    }

    protected function fileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return number_format($bytes / 1048576, 1) . ' MB';
    }
}
