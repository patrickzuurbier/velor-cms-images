<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration\Console;

use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Testing\PendingCommand;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;
use Velor\Images\Models\Image;
use Velor\Images\Tests\Concerns\UsesFakeS3Disk;

require_once __DIR__.'/../../Concerns/UsesFakeS3Disk.php';

class RecoverImagesCommandTest extends AbstractDatabaseIntegrationTestCase
{
    use UsesFakeS3Disk;

    public function test_it_recovers_missing_image_rows_from_s3(): void
    {
        $this->fakeS3Disk();
        $imageId = (string) Str::uuid();
        $original = UploadedFile::fake()->image('lost.jpg', 640, 480);
        $thumbnail = UploadedFile::fake()->image('thumbnail.webp', 320, 240);
        $disk = $this->s3Disk();
        $disk->putFileAs("images/{$imageId}", $original, 'original.jpg');
        $disk->putFileAs("images/{$imageId}", $thumbnail, 'thumbnail.webp');

        $command = $this->artisan('velor:images:recover');

        $this->assertInstanceOf(PendingCommand::class, $command);
        $command
            ->expectsOutput('Recovered 1 images from S3.')
            ->assertExitCode(Command::SUCCESS)
            ->run();

        $this->assertDatabaseHas('images', [
            'id'                => $imageId,
            'filename'          => 'original.jpg',
            'name'              => 'Recovered image '.Str::substr($imageId, 0, 8),
            'image_category_id' => null,
            'sort_order'        => 1,
            'description'       => null,
        ]);

        $image = Image::query()->findOrFail($imageId);
        $this->assertSame(640, $image->metaData()['original']['width']);
        $this->assertSame(480, $image->metaData()['original']['height']);
        $this->assertSame('image/jpeg', $image->metaData()['original']['mime_type']);
        $this->assertSame(320, $image->metaData()['formats']['thumbnail']['width']);

        $secondCommand = $this->artisan('velor:images:recover');

        $this->assertInstanceOf(PendingCommand::class, $secondCommand);
        $secondCommand
            ->expectsOutput('Recovered 0 images from S3.')
            ->assertExitCode(Command::SUCCESS)
            ->run();
    }

    public function test_dry_run_does_not_insert_rows(): void
    {
        $this->fakeS3Disk();
        $imageId = (string) Str::uuid();
        $original = UploadedFile::fake()->image('lost.png', 200, 100);
        $this->s3Disk()->putFileAs("images/{$imageId}", $original, 'original.png');

        $command = $this->artisan('velor:images:recover', ['--dry-run' => true]);

        $this->assertInstanceOf(PendingCommand::class, $command);
        $command
            ->expectsOutput('Found 1 images that can be recovered. No database rows were inserted.')
            ->assertExitCode(Command::SUCCESS)
            ->run();

        $this->assertDatabaseMissing('images', ['id' => $imageId]);
    }

    public function test_it_skips_existing_image_rows(): void
    {
        $this->fakeS3Disk();
        $image = Image::factory()->create([
            'filename'   => 'existing.jpg',
            'name'       => 'Existing image',
            'sort_order' => 7,
        ]);
        $original = UploadedFile::fake()->image('existing.jpg', 640, 480);
        $this->s3Disk()->putFileAs("images/{$image->id}", $original, 'original.jpg');

        $command = $this->artisan('velor:images:recover');

        $this->assertInstanceOf(PendingCommand::class, $command);
        $command
            ->expectsOutput('Recovered 0 images from S3.')
            ->assertExitCode(Command::SUCCESS)
            ->run();

        $this->assertSame(1, Image::query()->whereKey($image->id)->count());
        $this->assertSame('Existing image', $image->refresh()->name);
        $this->assertSame(1, $image->sort_order);
    }

    public function test_it_skips_unreadable_originals(): void
    {
        $this->fakeS3Disk();
        $imageId = (string) Str::uuid();
        $this->s3Disk()->put("images/{$imageId}/original.jpg", 'not an image');

        $command = $this->artisan('velor:images:recover');

        $this->assertInstanceOf(PendingCommand::class, $command);
        $command
            ->expectsOutput("Skipped images/{$imageId}/original.jpg: Object is not a readable image.")
            ->expectsOutput('Recovered 0 images from S3.')
            ->assertExitCode(Command::SUCCESS)
            ->run();

        $this->assertDatabaseMissing('images', ['id' => $imageId]);
    }
}
