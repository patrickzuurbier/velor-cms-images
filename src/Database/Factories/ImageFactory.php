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
            'filename'          => $this->faker->slug() . '.jpg',
            'name'              => $this->faker->words(2, true),
            'image_category_id' => null,
            'description'       => null,
            'meta_data'         => [
                'original' => [
                    'extension' => 'jpg',
                    'mime_type' => 'image/jpeg',
                    'size'      => $this->faker->numberBetween(10000, 500000),
                    'width'     => $this->faker->numberBetween(800, 2400),
                    'height'    => $this->faker->numberBetween(600, 1600),
                ],
            ],
        ];
    }
}
