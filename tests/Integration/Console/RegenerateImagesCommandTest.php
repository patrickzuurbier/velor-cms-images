<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration\Console;

use Velor\Images\Services\Contracts\ImagePathGeneratorInterface;
use Velor\Images\Services\Contracts\ImageUploadServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\PendingCommand;
use Tests\Concerns\UsesStorage;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;

class RegenerateImagesCommandTest extends AbstractDatabaseIntegrationTestCase
{
    use UsesStorage;

    protected function tearDown(): void
    {
        $this->filesystem()->disk('s3')->deleteDirectory('images');

        parent::tearDown();
    }

    public function test_it_regenerates_images_from_the_console_command(): void
    {
        $this->fakeS3Disk();

        $image = $this->imageUploadService()->upload(
            UploadedFile::fake()->image('console-regenerate.jpg', 1200, 800),
        );

        $disk = $this->filesystem()->disk('s3');

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

        $command = $this->artisan('velor:images:regenerate', [
            '--chunk' => 1,
        ]);

        $this->assertInstanceOf(PendingCommand::class, $command);
        $command
            ->expectsOutput('Regenerated formats for 1 images.')
            ->assertExitCode(Command::SUCCESS)
            ->run();

        $image->refresh();

        $this->assertFalse($disk->exists($this->pathGenerator()->format($image, 'half')));
        $this->assertTrue($disk->exists($this->pathGenerator()->format($image, 'tiny')));
        $this->assertSame(['tiny'], array_keys($image->metaData()['formats']));
    }

    protected function imageUploadService(): ImageUploadServiceInterface
    {
        return $this->app->make(ImageUploadServiceInterface::class);
    }

    protected function pathGenerator(): ImagePathGeneratorInterface
    {
        return $this->app->make(ImagePathGeneratorInterface::class);
    }
}
