<?php

declare(strict_types=1);

namespace Velor\Images\Services\Contracts;

interface ImagePickerDataFactoryInterface
{
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
    public function make(): array;
}
