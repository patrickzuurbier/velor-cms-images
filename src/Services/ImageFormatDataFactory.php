<?php

declare(strict_types=1);

namespace Velor\Images\Services;

use Velor\Images\Models\Image;
use Velor\Images\Services\Contracts\ImageFormatDataFactoryInterface;
use Velor\Images\Services\Contracts\ImageUrlGeneratorInterface;
use Illuminate\Support\Str;

class ImageFormatDataFactory implements ImageFormatDataFactoryInterface
{
    public function __construct(
        protected ImageUrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return array<string, array{
     *     label: string,
     *     url: string,
     *     extension: string,
     *     mime_type: string,
     *     size: int,
     *     size_label: string,
     *     width: int,
     *     height: int
     * }>
     */
    public function make(Image $image): array
    {
        $metaData = $image->metaData();
        $formats = [];

        $generatedFormats = $metaData['formats'] ?? [];

        if (! is_array($generatedFormats)) {
            return $formats;
        }

        foreach ($generatedFormats as $key => $formatMetaData) {
            if (! is_string($key) || ! is_array($formatMetaData)) {
                continue;
            }

            $formats[$key] = $this->formatData(
                key: $key,
                url: $this->urlGenerator->format($image, $key),
                metaData: $formatMetaData,
            );
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
     *     height: int
     * }
     */
    protected function formatData(string $key, string $url, array $metaData): array
    {
        return [
            'label'      => Str::headline($key),
            'url'        => $url,
            'extension'  => (string) ($metaData['extension'] ?? ''),
            'mime_type'  => (string) ($metaData['mime_type'] ?? ''),
            'size'       => (int) ($metaData['size'] ?? 0),
            'size_label' => $this->fileSize((int) ($metaData['size'] ?? 0)),
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
