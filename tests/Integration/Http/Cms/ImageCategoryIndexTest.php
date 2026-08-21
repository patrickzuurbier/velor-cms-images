<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration\Http\Cms;

use Velor\Images\Models\Image;
use Velor\Images\Models\ImageCategory;
use Tests\Concerns\UsesAuthorization;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;

class ImageCategoryIndexTest extends AbstractDatabaseIntegrationTestCase
{
    use UsesAuthorization;

    public function test_image_index_can_be_filtered_by_common_images(): void
    {
        $this->actingAsAdmin();
        $category = ImageCategory::factory()->create(['name' => 'News']);

        Image::factory()->create([
            'name'              => 'Common image',
            'image_category_id' => null,
            'sort_order'        => 1,
        ]);
        Image::factory()->create([
            'name'              => 'News image',
            'image_category_id' => $category->id,
            'sort_order'        => 1,
        ]);

        $response = $this->get(route('images.index', ['image_category' => 'common']));

        $response->assertOk();
        $response->assertSee('Common image');
        $response->assertDontSee('News image');
    }

    public function test_image_index_can_be_filtered_by_category(): void
    {
        $this->actingAsAdmin();
        $news = ImageCategory::factory()->create(['name' => 'News']);
        $slides = ImageCategory::factory()->create(['name' => 'Slides']);

        Image::factory()->create([
            'name'              => 'News image',
            'image_category_id' => $news->id,
            'sort_order'        => 1,
        ]);
        Image::factory()->create([
            'name'              => 'Slides image',
            'image_category_id' => $slides->id,
            'sort_order'        => 1,
        ]);

        $response = $this->get(route('images.index', ['image_category' => $news->id]));

        $response->assertOk();
        $response->assertSee('News image');
        $response->assertDontSee('Slides image');
    }

    public function test_image_index_shows_category_tabs(): void
    {
        $this->actingAsAdmin();

        ImageCategory::factory()->create(['name' => 'News']);
        ImageCategory::factory()->create(['name' => 'Slides']);

        $response = $this->get(route('images.index'));

        $response->assertOk();
        $response->assertSee('All');
        $response->assertSee('Common');
        $response->assertSee('News');
        $response->assertSee('Slides');
    }

    public function test_image_categories_require_authentication(): void
    {
        $response = $this->get(route('image-categories.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_image_rows_can_be_reordered_inside_category(): void
    {
        $this->actingAsAdmin();
        $category = ImageCategory::factory()->create(['name' => 'News']);
        $first = Image::factory()->create([
            'name'              => 'First image',
            'image_category_id' => $category->id,
            'sort_order'        => 1,
        ]);
        $second = Image::factory()->create([
            'name'              => 'Second image',
            'image_category_id' => $category->id,
            'sort_order'        => 2,
        ]);

        $response = $this->postJson(route('resource-row-order.update', ['resource' => 'images']), [
            'images' => [
                (string) $second->id,
                (string) $first->id,
            ],
            'context_key'   => 'image_category_id',
            'context_value' => (string) $category->id,
        ]);

        $response->assertNoContent();
        $this->assertSame(2, $first->refresh()->sort_order);
        $this->assertSame(1, $second->refresh()->sort_order);
    }

    public function test_common_image_rows_can_be_reordered_without_context(): void
    {
        $this->actingAsAdmin();
        $first = Image::factory()->create([
            'name'              => 'First image',
            'image_category_id' => null,
            'sort_order'        => 1,
        ]);
        $second = Image::factory()->create([
            'name'              => 'Second image',
            'image_category_id' => null,
            'sort_order'        => 2,
        ]);

        $response = $this->postJson(route('resource-row-order.update', ['resource' => 'images']), [
            'images' => [
                (string) $second->id,
                (string) $first->id,
            ],
            'context_key' => 'image_category_id',
        ]);

        $response->assertNoContent();
        $this->assertSame(2, $first->refresh()->sort_order);
        $this->assertSame(1, $second->refresh()->sort_order);
    }

    public function test_image_order_can_be_changed_when_editing_image(): void
    {
        $this->actingAsAdmin();
        $category = ImageCategory::factory()->create(['name' => 'News']);
        $first = Image::factory()->create([
            'name'              => 'First image',
            'image_category_id' => $category->id,
            'sort_order'        => 1,
        ]);
        $second = Image::factory()->create([
            'name'              => 'Second image',
            'image_category_id' => $category->id,
            'sort_order'        => 2,
        ]);

        $response = $this->put(route('images.update', ['image' => $second->id]), [
            'name'              => 'Second image',
            'image_category_id' => (string) $category->id,
            'sort_order'        => 1,
            'description'       => [
                'en' => null,
                'nl' => null,
            ],
        ]);

        $response->assertRedirect(route('images.show', ['image' => $second->id]));
        $this->assertSame(2, $first->refresh()->sort_order);
        $this->assertSame(1, $second->refresh()->sort_order);
    }
}
