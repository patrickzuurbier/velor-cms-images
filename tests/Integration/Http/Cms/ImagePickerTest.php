<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration\Http\Cms;

use Velor\Images\Models\Image;
use Illuminate\Contracts\Config\Repository;
use Tests\Concerns\UsesAuthorization;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;

class ImagePickerTest extends AbstractDatabaseIntegrationTestCase
{
    use UsesAuthorization;

    public function test_image_picker_returns_images_for_rich_text_insertion(): void
    {
        $this->actingAsAdmin();

        $image = Image::factory()->create([
            'name'        => 'Hero image',
            'description' => [
                'en' => 'A useful description',
                'nl' => 'Een bruikbare omschrijving',
            ],
        ]);

        $response = $this->getJson(route('images.picker'));

        $response->assertOk();
        $response->assertJsonPath('images.0.id', (string) $image->getKey());
        $response->assertJsonPath('images.0.name', 'Hero image');
        $response->assertJsonPath('images.0.description', 'A useful description');
        $response->assertJsonPath('images.0.alt', 'A useful description');
        $response->assertJsonPath('images.0.insert_format', 'half');
        $this->assertStringEndsWith(
            sprintf('/images/%s/half.webp', $image->getKey()),
            (string) $response->json('images.0.formats.half'),
        );
        $this->assertStringEndsWith(
            sprintf('/images/%s/full.webp', $image->getKey()),
            (string) $response->json('images.0.formats.full'),
        );
        $this->assertStringEndsWith(
            sprintf('/images/%s/thumbnail.webp', $image->getKey()),
            (string) $response->json('images.0.thumbnail_url'),
        );
        $this->assertStringEndsWith(
            sprintf('/images/%s/half.webp', $image->getKey()),
            (string) $response->json('images.0.insert_url'),
        );
    }

    public function test_image_picker_uses_the_active_locale_for_default_alt_text(): void
    {
        $user = $this->actingAsAdmin();
        $user->update([
            'settings' => [
                'locale' => 'nl',
            ],
        ]);

        Image::factory()->create([
            'name'        => 'Hero image',
            'description' => [
                'en' => 'English description',
                'nl' => 'Nederlandse omschrijving',
            ],
        ]);

        $response = $this->getJson(route('images.picker'));

        $response->assertOk();
        $response->assertJsonPath('images.0.description', 'Nederlandse omschrijving');
        $response->assertJsonPath('images.0.alt', 'Nederlandse omschrijving');
    }

    public function test_image_picker_requires_authentication(): void
    {
        $response = $this->getJson(route('images.picker'));

        $response->assertUnauthorized();
    }

    public function test_image_picker_formats_are_configurable(): void
    {
        $this->actingAsAdmin();
        $config = $this->app->make(Repository::class);

        $config->set('velor-images.picker.default_format', 'full');
        $config->set('velor-images.picker.formats', ['full']);

        $image = Image::factory()->create([
            'name' => 'Configurable image',
        ]);

        $response = $this->getJson(route('images.picker'));

        $response->assertOk();
        $response->assertJsonPath('images.0.insert_format', 'full');
        $this->assertStringEndsWith(
            sprintf('/images/%s/full.webp', $image->getKey()),
            (string) $response->json('images.0.insert_url'),
        );
        $this->assertSame(
            ['full'],
            array_keys((array) $response->json('images.0.formats')),
        );
    }

    public function test_image_picker_falls_back_to_configured_image_formats_without_cms(): void
    {
        $this->actingAsAdmin();
        $config = $this->app->make(Repository::class);

        $config->set('velor-images.picker.default_format', 'half');
        $config->set('velor-images.picker.formats', []);
        $config->set('velor-images.formats', [
            'thumbnail' => [],
            'full'      => [],
            'cms'       => [],
        ]);

        $image = Image::factory()->create([
            'name' => 'Fallback image',
        ]);

        $response = $this->getJson(route('images.picker'));

        $response->assertOk();
        $response->assertJsonPath('images.0.insert_format', 'thumbnail');
        $this->assertStringEndsWith(
            sprintf('/images/%s/thumbnail.webp', $image->getKey()),
            (string) $response->json('images.0.insert_url'),
        );
        $this->assertSame(
            ['thumbnail', 'full'],
            array_keys((array) $response->json('images.0.formats')),
        );
    }
}
