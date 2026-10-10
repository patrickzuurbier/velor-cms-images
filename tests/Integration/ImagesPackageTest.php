<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration;

use App\Models\Image;
use App\Services\Authorization\Contracts\PolicyRegistryInterface;
use App\Services\CmsMenu\Contracts\CmsMenuItemRegistryInterface;
use App\Services\CmsMenu\Data\CmsMenuItemData;
use App\Services\Resources\Contracts\ResourceRegistryInterface;
use Illuminate\Contracts\Translation\Translator;
use Tests\Integration\AbstractIntegrationTestCase;
use Velor\Images\Policies\ImagePolicy;
use Velor\Images\Resources\ImageResource;

class ImagesPackageTest extends AbstractIntegrationTestCase
{
    public function test_it_registers_the_image_resource(): void
    {
        $registry = $this->app->make(ResourceRegistryInterface::class);

        $this->assertInstanceOf(ImageResource::class, $registry->resourceFor(Image::class));
    }

    public function test_it_registers_the_image_policy_for_privileges(): void
    {
        $registry = $this->app->make(PolicyRegistryInterface::class);

        $this->assertSame(ImagePolicy::class, $registry->privilegePolicies()[Image::class]);
    }

    public function test_it_registers_the_images_cms_menu_item(): void
    {
        $registry = $this->app->make(CmsMenuItemRegistryInterface::class);
        $item = collect($registry->items())->firstWhere('routeName', 'images.index');

        $this->assertInstanceOf(CmsMenuItemData::class, $item);
        $this->assertSame(Image::class, $item->modelClass);
        $this->assertSame('velor-images::resources.images.plural', $item->label);
        $this->assertSame('bi-images', $item->icon);
    }

    public function test_it_loads_image_cms_routes(): void
    {
        $this->app['router']->getRoutes()->refreshNameLookups();

        $route = $this->app['router']->getRoutes()->getByName('images.index');

        $this->assertNotNull($route);
        $this->assertSame('cms/images', $route->uri());
        $this->assertContains('web', $route->middleware());
        $this->assertContains('auth', $route->middleware());
    }

    public function test_it_loads_the_image_picker_route_before_the_resource_show_route(): void
    {
        $this->app['router']->getRoutes()->refreshNameLookups();

        $route = $this->app['router']->getRoutes()->getByName('images.picker');

        $this->assertNotNull($route);
        $this->assertSame('cms/images/picker', $route->uri());
        $this->assertContains('auth', $route->middleware());
    }

    public function test_it_loads_image_resource_translations_from_package_namespace(): void
    {
        $translator = $this->app->make(Translator::class);

        $translator->setLocale('en');

        $this->assertSame('Images', $translator->get('velor-images::resources.images.plural'));
        $this->assertSame('Unused', $translator->get('velor-images::resources.images.tabs.unused'));

        $translator->setLocale('nl');

        $this->assertSame('Afbeeldingen', $translator->get('velor-images::resources.images.plural'));
        $this->assertSame('Ongebruikt', $translator->get('velor-images::resources.images.tabs.unused'));
    }
}
