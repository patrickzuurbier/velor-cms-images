<?php

declare(strict_types=1);

namespace Velor\Images\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Velor\Images\Models\ImageCategory;

/**
 * @extends Factory<\Velor\Images\Models\ImageCategory>
 */
class ImageCategoryFactory extends Factory
{
    protected $model = ImageCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
        ];
    }
}
