<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration;

use App\Models\Image;
use App\Models\ImageUsage;
use App\Resources\Tabs\ResourceTab;
use Illuminate\Http\Request;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;
use Velor\Images\Resources\ImageResource;

class ImageResourceTest extends AbstractDatabaseIntegrationTestCase
{
    public function test_it_builds_tabs_from_actual_resource_types_with_unused_last(): void
    {
        $this->app->setLocale('en');

        ImageUsage::factory()->create(['resource_type' => 'pages']);
        ImageUsage::factory()->create(['resource_type' => 'articles']);

        $resource = $this->app->make(ImageResource::class);
        $tabs = $resource->tabs();

        $this->assertSame(['Articles', 'Pages', 'Unused'], array_map(
            static fn (ResourceTab $tab): string => $tab->getLabel(),
            $tabs,
        ));
        $this->assertTrue($tabs[0]->isActive(Request::create('/cms/images')));
        $this->assertTrue($tabs[1]->isActive(Request::create('/cms/images', 'GET', [
            'scope' => 'resource:pages',
        ])));
        $this->assertTrue($tabs[2]->isActive(Request::create('/cms/images', 'GET', [
            'scope' => 'unused',
        ])));
    }

    public function test_it_uses_the_requested_resource_or_unused_scope_for_the_index(): void
    {
        $used = Image::factory()->create();
        $unused = Image::factory()->create();
        ImageUsage::factory()->for($used)->create(['resource_type' => 'pages']);

        $this->setRequest(['scope' => 'resource:pages']);

        $this->assertSame([$used->getKey()], $this->app->make(ImageResource::class)->indexQuery()->pluck('id')->all());

        $this->setRequest(['scope' => 'unused']);

        $this->assertSame([$unused->getKey()], $this->app->make(ImageResource::class)->indexQuery()->pluck('id')->all());
    }

    /**
     * @param array<string, string> $query
     */
    protected function setRequest(array $query): void
    {
        $request = Request::create('/cms/images', 'GET', $query);

        $this->app->instance('request', $request);
        $this->app->instance(Request::class, $request);
    }
}
