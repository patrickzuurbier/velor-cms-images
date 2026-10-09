<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration;

use App\Models\ImageUsage;
use App\Models\Image;
use App\Models\User;
use App\Services\Images\Contracts\ImagePathGeneratorInterface;
use App\Services\Resources\Contracts\ResourceBulkDeleteServiceInterface;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\Request;
use Tests\Concerns\UsesAuthorization;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;

class ImageResourceBulkDeleteServiceTest extends AbstractDatabaseIntegrationTestCase
{
    use UsesAuthorization;

    public function test_it_physically_deletes_unused_images(): void
    {
        $admin = $this->actingAsAdmin();
        $this->app->make(Request::class)->setUserResolver(static fn (): User => $admin);

        $image = Image::factory()->create();
        $pathGenerator = $this->app->make(ImagePathGeneratorInterface::class);
        $filesystem = $this->app->make(FilesystemManager::class);
        $config = $this->app->make(Repository::class);
        $diskName = $config->get('images.disk');

        $this->assertIsString($diskName);

        $disk = $filesystem->disk($diskName);
        $path = $pathGenerator->original($image);
        $disk->put($path, 'image contents');

        $deleted = $this->app->make(ResourceBulkDeleteServiceInterface::class)->delete(
            Image::class,
            [(string) $image->getKey()],
        );

        $this->assertSame(1, $deleted);
        $this->assertDatabaseMissing('images', ['id' => $image->getKey()]);
        $this->assertFalse($disk->exists($path));
    }

    public function test_it_does_not_delete_used_images(): void
    {
        $admin = $this->actingAsAdmin();
        $this->app->make(Request::class)->setUserResolver(static fn (): User => $admin);

        $image = Image::factory()->create();
        ImageUsage::factory()->for($image)->create();
        $service = $this->app->make(ResourceBulkDeleteServiceInterface::class);

        try {
            $service->delete(Image::class, [(string) $image->getKey()]);
            $this->fail('A used image should not be deletable.');
        } catch (AuthorizationException) {
            $this->assertDatabaseHas('images', ['id' => $image->getKey()]);
        }
    }

    public function test_it_delegates_other_resource_types_to_the_core_service(): void
    {
        $admin = $this->actingAsAdmin();
        $this->app->make(Request::class)->setUserResolver(static fn (): User => $admin);
        $user = User::factory()->create();

        $deleted = $this->app->make(ResourceBulkDeleteServiceInterface::class)->delete(
            User::class,
            [(string) $user->getKey()],
        );

        $this->assertSame(1, $deleted);
        $this->assertDatabaseMissing('users', ['id' => $user->getKey()]);
    }
}
