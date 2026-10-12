<?php

declare(strict_types=1);

namespace Velor\Images\Factories\Contracts;

use App\Models\Image;

interface ImagePickerDataFactoryInterface
{
    /**
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     filename: string,
     *     preview_url: string,
     *     formats: array<string, string>,
     * }>
     */
    public function make(): array;

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     filename: string,
     *     preview_url: string,
     *     formats: array<string, string>,
     * }
     */
    public function makeFor(Image $image): array;
}
