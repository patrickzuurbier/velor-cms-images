<?php

declare(strict_types=1);

namespace Velor\Images\Factories\Contracts;

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
}
