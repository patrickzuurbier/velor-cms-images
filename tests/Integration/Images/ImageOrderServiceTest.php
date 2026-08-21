<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration\Images;

use App\Services\Resources\Contracts\ResourceRowOrderServiceInterface;
use Tests\Concerns\UsesAuthorization;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;
use Velor\Images\Models\Image;
use Velor\Images\Models\ImageCategory;
use Velor\Images\Services\Contracts\ImageOrderServiceInterface;

class ImageOrderServiceTest extends AbstractDatabaseIntegrationTestCase
{
    use UsesAuthorization;

    public function test_next_sort_order_is_scoped_to_category(): void
    {
        $news = ImageCategory::factory()->create(['name' => 'News']);
        $slides = ImageCategory::factory()->create(['name' => 'Slides']);

        Image::factory()->create([
            'image_category_id' => $news->id,
            'sort_order'        => 1,
        ]);
        Image::factory()->create([
            'image_category_id' => $news->id,
            'sort_order'        => 2,
        ]);
        Image::factory()->create([
            'image_category_id' => $slides->id,
            'sort_order'        => 1,
        ]);
        Image::factory()->create([
            'image_category_id' => null,
            'sort_order'        => 1,
        ]);

        $service = $this->app->make(ImageOrderServiceInterface::class);

        $this->assertSame(3, $service->nextSortOrder((string) $news->id));
        $this->assertSame(2, $service->nextSortOrder((string) $slides->id));
        $this->assertSame(2, $service->nextSortOrder(null));
    }

    public function test_moving_image_to_another_category_places_it_at_the_end(): void
    {
        $news = ImageCategory::factory()->create(['name' => 'News']);
        $slides = ImageCategory::factory()->create(['name' => 'Slides']);

        Image::factory()->create([
            'image_category_id' => $slides->id,
            'sort_order'        => 1,
        ]);
        Image::factory()->create([
            'image_category_id' => $slides->id,
            'sort_order'        => 2,
        ]);

        $image = Image::factory()->create([
            'image_category_id' => $news->id,
            'sort_order'        => 1,
        ]);

        $service = $this->app->make(ImageOrderServiceInterface::class);
        $service->moveToCategory($image, (string) $slides->id);

        $this->assertSame((string) $slides->id, $image->refresh()->image_category_id);
        $this->assertSame(3, $image->sort_order);
    }

    public function test_moving_image_to_order_rewrites_category_order(): void
    {
        $category = ImageCategory::factory()->create(['name' => 'News']);
        $first = Image::factory()->create([
            'image_category_id' => $category->id,
            'sort_order'        => 1,
        ]);
        $second = Image::factory()->create([
            'image_category_id' => $category->id,
            'sort_order'        => 2,
        ]);
        $third = Image::factory()->create([
            'image_category_id' => $category->id,
            'sort_order'        => 3,
        ]);

        $service = $this->app->make(ImageOrderServiceInterface::class);
        $service->moveToCategoryAt($third, (string) $category->id, 1);

        $this->assertSame(2, $first->refresh()->sort_order);
        $this->assertSame(3, $second->refresh()->sort_order);
        $this->assertSame(1, $third->refresh()->sort_order);
    }

    public function test_reordering_visible_images_compacts_order_positions(): void
    {
        $category = ImageCategory::factory()->create(['name' => 'News']);
        $first = Image::factory()->create([
            'image_category_id' => $category->id,
            'sort_order'        => 10,
        ]);
        $second = Image::factory()->create([
            'image_category_id' => $category->id,
            'sort_order'        => 20,
        ]);
        $third = Image::factory()->create([
            'image_category_id' => $category->id,
            'sort_order'        => 30,
        ]);

        $service = $this->app->make(ResourceRowOrderServiceInterface::class);
        $service->reorder(
            modelClass: Image::class,
            orderColumn: 'sort_order',
            ids: [
                (string) $third->id,
                (string) $first->id,
                (string) $second->id,
            ],
            scopeColumn: 'image_category_id',
            scopeValue: (string) $category->id,
        );

        $this->assertSame(2, $first->refresh()->sort_order);
        $this->assertSame(3, $second->refresh()->sort_order);
        $this->assertSame(1, $third->refresh()->sort_order);
    }

    public function test_deleting_category_moves_images_to_common_without_order_collisions(): void
    {
        $this->actingAsAdmin();
        $category = ImageCategory::factory()->create(['name' => 'News']);

        Image::factory()->create([
            'image_category_id' => null,
            'sort_order'        => 1,
        ]);
        $first = Image::factory()->create([
            'image_category_id' => $category->id,
            'sort_order'        => 1,
        ]);
        $second = Image::factory()->create([
            'image_category_id' => $category->id,
            'sort_order'        => 2,
        ]);

        $response = $this->delete(route('image-categories.destroy', ['image_category' => $category->id]));

        $response->assertRedirect(route('image-categories.index'));
        $this->assertDatabaseMissing('image_categories', ['id' => $category->id]);
        $this->assertSame(2, $first->refresh()->sort_order);
        $this->assertSame(3, $second->refresh()->sort_order);
        $this->assertNull($first->image_category_id);
        $this->assertNull($second->image_category_id);
    }
}
