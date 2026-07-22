<?php

declare(strict_types=1);

namespace Velor\Images\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Velor\Images\Models\Image;

/**
 * @extends Factory<\Velor\Images\Models\Image>
 */
class ImageFactory extends Factory
{
    protected $model = Image::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'filename'          => fake()->slug() . '.jpg',
            'name'              => fake()->words(2, true),
            'image_category_id' => null,
            'sort_order'        => fake()->unique()->numberBetween(1, 100000),
            'description'       => null,
            'meta_data'         => [
                'original' => [
                    'extension' => 'jpg',
                    'mime_type' => 'image/jpeg',
                    'size'      => fake()->numberBetween(10000, 500000),
                    'width'     => fake()->numberBetween(800, 2400),
                    'height'    => fake()->numberBetween(600, 1600),
                ],
            ],
        ];
    }
}
