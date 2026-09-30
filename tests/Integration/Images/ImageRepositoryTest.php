<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration\Images;

use Tests\Integration\AbstractDatabaseIntegrationTestCase;
use Velor\Images\Models\Image;
use Velor\Images\Models\ImageCategory;
use Velor\Images\Repositories\Contracts\ImageRepositoryInterface;

class ImageRepositoryTest extends AbstractDatabaseIntegrationTestCase
{
    protected ImageRepositoryInterface $imageRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->imageRepository = $this->app->make(ImageRepositoryInterface::class);
    }

    public function test_it_builds_an_index_query_for_a_category(): void
    {
        $category = ImageCategory::factory()->create();
        $categoryImage = Image::factory()->for($category, 'category')->create();
        Image::factory()->create();

        $images = $this->imageRepository
            ->indexQuery((string) $category->getKey())
            ->get();

        $this->assertCount(1, $images);
        $this->assertTrue($images->first()?->is($categoryImage));
        $this->assertTrue($images->first()->relationLoaded('category'));
    }

    public function test_it_builds_an_index_query_for_common_images(): void
    {
        $category = ImageCategory::factory()->create();
        $commonImage = Image::factory()->create();
        Image::factory()->for($category, 'category')->create();

        $images = $this->imageRepository
            ->indexQuery('common')
            ->get();

        $this->assertCount(1, $images);
        $this->assertTrue($images->first()?->is($commonImage));
    }

    public function test_it_returns_images_ordered_for_picker(): void
    {
        Image::factory()->create(['name' => 'Zulu image']);
        Image::factory()->create(['name' => 'Alpha image']);

        $images = $this->imageRepository->orderedForPicker();

        $this->assertSame('Alpha image', $images->first()?->getAttribute('name'));
    }

    public function test_it_returns_existing_ids(): void
    {
        $image = Image::factory()->create();

        $ids = $this->imageRepository->existingIds();

        $this->assertTrue($ids[(string) $image->getKey()] ?? false);
    }

    public function test_it_returns_ordered_ids_for_category(): void
    {
        $category = ImageCategory::factory()->create();
        $first = Image::factory()->for($category, 'category')->create(['sort_order' => 1]);
        $second = Image::factory()->for($category, 'category')->create(['sort_order' => 2]);
        Image::factory()->create(['sort_order' => 1]);

        $ids = $this->imageRepository->orderedIdsForCategory((string) $category->getKey());

        $this->assertSame([
            (string) $first->getKey(),
            (string) $second->getKey(),
        ], $ids);
    }
}
