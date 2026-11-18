<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration;

use App\Models\Image;
use App\Models\Role;
use App\Models\User;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;

class ImageAuthorizationTest extends AbstractDatabaseIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create([
            'name'       => 'Unauthorized',
            'slug'       => 'unauthorized',
            'privileges' => [],
        ]);

        $this->actingAs(User::factory()->create([
            'role_id' => $role->getKey(),
        ]));
    }

    public function test_image_read_routes_require_their_policy_privileges(): void
    {
        $image = Image::factory()->create();

        $this->getJson(route('images.index'))->assertForbidden();
        $this->getJson(route('images.create'))->assertForbidden();
        $this->getJson(route('images.show', ['image' => $image]))->assertForbidden();
    }

    public function test_image_write_routes_require_their_policy_privileges(): void
    {
        $image = Image::factory()->create(['name' => 'Original image']);

        $this->postJson(route('images.store'))->assertForbidden();
        $this->putJson(route('images.update', ['image' => $image]))->assertForbidden();
        $this->deleteJson(route('images.destroy', ['image' => $image]))->assertForbidden();

        $this->assertSame('Original image', $image->refresh()->getAttribute('name'));
        $this->assertSame(1, Image::query()->count());
    }
}
