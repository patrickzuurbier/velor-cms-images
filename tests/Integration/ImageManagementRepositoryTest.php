<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration;

use App\Models\Image;
use App\Models\ImageUsage;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;
use Velor\Images\Repositories\Contracts\ImageManagementRepositoryInterface;

class ImageManagementRepositoryTest extends AbstractDatabaseIntegrationTestCase
{
    protected ImageManagementRepositoryInterface $imageManagementRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->imageManagementRepository = $this->app->make(ImageManagementRepositoryInterface::class);
    }

    public function test_it_returns_the_actual_resource_types_in_stable_order(): void
    {
        ImageUsage::factory()->create(['resource_type' => 'pages']);
        ImageUsage::factory()->create(['resource_type' => 'articles']);
        ImageUsage::factory()->create(['resource_type' => 'pages']);

        $this->assertSame(['articles', 'pages'], $this->imageManagementRepository->resourceTypes());
    }

    public function test_an_image_used_by_multiple_resource_types_appears_in_each_matching_query(): void
    {
        $image = Image::factory()->create();
        ImageUsage::factory()->for($image)->create(['resource_type' => 'pages']);
        ImageUsage::factory()->for($image)->create(['resource_type' => 'paragraphs']);

        $this->assertTrue($this->imageManagementRepository->indexQuery('pages')->whereKey($image->getKey())->exists());
        $this->assertTrue($this->imageManagementRepository->indexQuery('paragraphs')->whereKey($image->getKey())->exists());
    }

    public function test_unused_query_only_returns_images_without_usages(): void
    {
        $unused = Image::factory()->create();
        $used = Image::factory()->create();
        ImageUsage::factory()->for($used)->create();

        $this->assertTrue($this->imageManagementRepository->indexQuery(null)->whereKey($unused->getKey())->exists());
        $this->assertFalse($this->imageManagementRepository->indexQuery(null)->whereKey($used->getKey())->exists());
        $this->assertTrue($this->imageManagementRepository->isUnused($unused));
        $this->assertFalse($this->imageManagementRepository->isUnused($used));
    }

    public function test_picker_images_are_ordered_by_name_and_filename(): void
    {
        $second = Image::factory()->create(['name' => 'Beta', 'filename' => 'second.jpg']);
        $first = Image::factory()->create(['name' => 'Alpha', 'filename' => 'first.jpg']);
        $third = Image::factory()->create(['name' => 'Beta', 'filename' => 'third.jpg']);

        $this->assertSame(
            [$first->getKey(), $second->getKey(), $third->getKey()],
            $this->imageManagementRepository->pickerImages()->modelKeys(),
        );
    }

    public function test_it_updates_only_the_editable_image_name(): void
    {
        $image = Image::factory()->create([
            'filename' => 'original.jpg',
            'name'     => 'Original',
        ]);

        $this->imageManagementRepository->update($image, ['name' => 'Updated']);

        $image->refresh();

        $this->assertSame('Updated', $image->name);
        $this->assertSame('original.jpg', $image->filename);
    }
}
