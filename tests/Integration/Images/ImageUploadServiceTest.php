<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration\Images;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;
use Velor\Images\Models\ImageCategory;
use Velor\Images\Services\Contracts\ImageFormatGeneratorInterface;
use Velor\Images\Services\Contracts\ImagePathGeneratorInterface;
use Velor\Images\Services\Contracts\ImageUploadServiceInterface;

class ImageUploadServiceTest extends AbstractDatabaseIntegrationTestCase
{
    protected function tearDown(): void
    {
        $this->s3Disk()->deleteDirectory('images');

        parent::tearDown();
    }

    public function test_it_stores_original_and_configured_formats_for_uploaded_image(): void
    {
        Storage::fake('s3');
        $category = ImageCategory::factory()->create(['name' => 'News']);

        $image = $this->imageUploadService()->upload(
            UploadedFile::fake()->image('hero-image.jpg', 1200, 800),
            (string) $category->getKey(),
        );

        $disk = $this->s3Disk();
        $metaData = $image->metaData();

        $this->assertSame('hero-image.jpg', $image->getAttribute('filename'));
        $this->assertSame('Hero Image', $image->getAttribute('name'));
        $this->assertSame((string) $category->getKey(), $image->getAttribute('image_category_id'));
        $this->assertSame(1, $image->getAttribute('sort_order'));
        $this->assertSame('', $image->getAttribute('description'));
        $this->assertSame(1200, $metaData['original']['width']);
        $this->assertSame(800, $metaData['original']['height']);
        $this->assertSame('jpg', $metaData['original']['extension']);
        $this->assertSame('image/jpeg', $metaData['original']['mime_type']);
        $this->assertSame('webp', $metaData['formats']['full']['extension']);
        $this->assertSame('webp', $metaData['formats']['cms']['extension']);
        $this->assertSame('image/webp', $metaData['formats']['thumbnail']['mime_type']);
        $this->assertTrue($disk->exists($this->pathGenerator()->original($image)));
        $this->assertTrue($disk->exists($this->pathGenerator()->format($image, 'full')));
        $this->assertTrue($disk->exists($this->pathGenerator()->format($image, 'half')));
        $this->assertTrue($disk->exists($this->pathGenerator()->format($image, 'cms')));
        $this->assertTrue($disk->exists($this->pathGenerator()->format($image, 'thumbnail')));
    }

    public function test_it_deletes_image_directory_and_model(): void
    {
        Storage::fake('s3');

        $image = $this->imageUploadService()->upload(
            UploadedFile::fake()->image('delete-me.jpg', 640, 480),
        );

        $directory = $this->pathGenerator()->directory((string) $image->getKey());

        $this->imageUploadService()->delete($image);

        $this->assertFalse($this->s3Disk()->exists($directory));
        $this->assertDatabaseMissing('images', ['id' => $image->id]);
    }

    public function test_it_removes_stale_formats_when_regenerating_image_formats(): void
    {
        Storage::fake('s3');

        $image = $this->imageUploadService()->upload(
            UploadedFile::fake()->image('regenerate-me.jpg', 1200, 800),
        );

        $disk = $this->s3Disk();

        $this->assertTrue($disk->exists($this->pathGenerator()->format($image, 'half')));

        $this->app['config']->set('velor-images.formats', [
            'tiny' => [
                'width'   => 120,
                'height'  => 80,
                'fit'     => 'cover',
                'format'  => 'webp',
                'quality' => 80,
                'upscale' => false,
            ],
        ]);

        $this->imageFormatGenerator()->regenerate($image);

        $image->refresh();

        $this->assertTrue($disk->exists($this->pathGenerator()->original($image)));
        $this->assertFalse($disk->exists($this->pathGenerator()->format($image, 'half')));
        $this->assertTrue($disk->exists($this->pathGenerator()->format($image, 'tiny')));
        $this->assertSame(['tiny'], array_keys($image->metaData()['formats']));
        $this->assertSame(120, $image->metaData()['formats']['tiny']['width']);
        $this->assertSame(80, $image->metaData()['formats']['tiny']['height']);
    }

    protected function imageUploadService(): ImageUploadServiceInterface
    {
        return $this->app->make(ImageUploadServiceInterface::class);
    }

    protected function imageFormatGenerator(): ImageFormatGeneratorInterface
    {
        return $this->app->make(ImageFormatGeneratorInterface::class);
    }

    protected function pathGenerator(): ImagePathGeneratorInterface
    {
        return $this->app->make(ImagePathGeneratorInterface::class);
    }

    protected function s3Disk(): Filesystem
    {
        return Storage::disk('s3');
    }
}
