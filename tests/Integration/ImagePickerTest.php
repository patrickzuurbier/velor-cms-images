<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration;

use App\Models\Image;
use Tests\Concerns\UsesAuthorization;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;

class ImagePickerTest extends AbstractDatabaseIntegrationTestCase
{
    use UsesAuthorization;

    public function test_it_returns_core_images_with_a_preview(): void
    {
        $this->actingAsAdmin();

        $image = Image::factory()->create([
            'name'      => 'Hero image',
            'filename'  => 'hero.jpg',
            'meta_data' => [
                'original' => ['extension' => 'jpg'],
                'formats'  => [
                    'full' => ['width' => 1920],
                    'half' => ['width' => 960],
                    'cms'  => ['width' => 960],
                ],
            ],
        ]);

        $response = $this->getJson(route('images.picker'));

        $response->assertOk();
        $response->assertJsonPath('images.0.id', (string) $image->getKey());
        $response->assertJsonPath('images.0.name', 'Hero image');
        $response->assertJsonPath('images.0.filename', 'hero.jpg');
        $response->assertJsonPath('labels.open', 'Select existing image');
        $response->assertJsonPath('labels.select', 'Select image');
        $this->assertStringEndsWith(
            sprintf('/images/%s/cms.jpg', $image->getKey()),
            (string) $response->json('images.0.preview_url'),
        );
        $response->assertJsonMissingPath('images.0.formats');
    }

    public function test_it_requires_authentication(): void
    {
        $this->getJson(route('images.picker'))->assertUnauthorized();
    }

    public function test_it_requires_image_view_permission(): void
    {
        $this->actingAsRole('guest');

        $this->getJson(route('images.picker'))->assertForbidden();
    }
}
