<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration;

use App\Models\Image;
use App\Services\Images\Contracts\ImageUploadServiceInterface;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\UsesAuthorization;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;

class ImagePickerTest extends AbstractDatabaseIntegrationTestCase
{
    use UsesAuthorization;

    public function test_it_returns_core_images_with_preview_and_generated_formats(): void
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
        $this->assertSame(['full', 'half'], array_keys((array) $response->json('images.0.formats')));
    }

    public function test_it_uploads_an_image_from_the_picker(): void
    {
        $this->actingAsAdmin();

        $image = Image::factory()->create([
            'name'      => 'Uploaded image',
            'filename'  => 'uploaded.jpg',
            'meta_data' => [
                'original' => ['extension' => 'jpg'],
                'formats'  => [
                    'full' => ['extension' => 'jpg'],
                    'cms'  => ['extension' => 'jpg'],
                ],
            ],
        ]);
        $uploadService = $this->createMock(ImageUploadServiceInterface::class);
        $uploadService
            ->expects($this->once())
            ->method('upload')
            ->with($this->isInstanceOf(UploadedFile::class))
            ->willReturn($image);
        $this->app->instance(ImageUploadServiceInterface::class, $uploadService);

        $response = $this->postJson(route('images.picker.upload'), [
            'image' => UploadedFile::fake()->image('uploaded.jpg'),
        ]);

        $response->assertCreated();
        $response->assertJsonPath('image.id', (string) $image->getKey());
        $response->assertJsonPath('image.name', 'Uploaded image');
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

    public function test_picker_upload_requires_image_create_permission(): void
    {
        $this->actingAsRole('guest');

        $this->postJson(route('images.picker.upload'), [
            'image' => UploadedFile::fake()->image('forbidden.jpg'),
        ])->assertForbidden();
    }
}
