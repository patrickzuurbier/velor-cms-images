<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration;

use App\Models\Image;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;
use Velor\Images\Factories\Contracts\ImageFormatDataFactoryInterface;

class ImageFormatDataFactoryTest extends AbstractDatabaseIntegrationTestCase
{
    public function test_it_builds_generated_format_view_data(): void
    {
        $image = Image::factory()->create([
            'meta_data' => [
                'formats' => [
                    'hero_image' => [
                        'extension' => 'webp',
                        'mime_type' => 'image/webp',
                        'size'      => 1536,
                        'width'     => 1920,
                        'height'    => 1080,
                    ],
                    'preview' => [
                        'extension' => 'webp',
                        'width'     => 320,
                        'height'    => 240,
                    ],
                    'thumbnail' => [
                        'extension' => 'webp',
                        'width'     => 150,
                        'height'    => 150,
                    ],
                ],
            ],
        ]);

        $formats = $this->app->make(ImageFormatDataFactoryInterface::class)->make($image);

        $this->assertSame(['hero_image'], array_keys($formats));
        $this->assertSame('Hero Image', $formats['hero_image']['label']);
        $this->assertStringEndsWith(
            sprintf('/images/%s/hero_image.webp', $image->getKey()),
            $formats['hero_image']['url'],
        );
        $this->assertSame('webp', $formats['hero_image']['extension']);
        $this->assertSame('image/webp', $formats['hero_image']['mime_type']);
        $this->assertSame(1536, $formats['hero_image']['size']);
        $this->assertSame('1.5 KB', $formats['hero_image']['size_label']);
        $this->assertSame(1920, $formats['hero_image']['width']);
        $this->assertSame(1080, $formats['hero_image']['height']);
    }
}
