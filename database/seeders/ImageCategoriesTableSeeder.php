<?php

declare(strict_types=1);

namespace Velor\Images\Database\Seeders;

use Illuminate\Database\Seeder;
use Velor\Images\Models\ImageCategory;

class ImageCategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['News', 'Slides', 'Logos', 'Paragraphs', 'Products'] as $name) {
            ImageCategory::firstOrCreate(['name' => $name]);
        }
    }
}
