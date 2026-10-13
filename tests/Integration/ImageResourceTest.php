<?php

declare(strict_types=1);

namespace Velor\Images\Tests\Integration;

use App\Models\Image;
use App\Models\ImageUsage;
use App\Data\View\ImagePreviewData;
use App\Resources\Fields\Field;
use App\Resources\Fields\ImageMetaData;
use App\Resources\Fields\ImagePreview;
use App\Resources\Fields\ImageUpload;
use App\Resources\Tabs\ResourceTab;
use App\Services\Images\Contracts\ImageUrlGeneratorInterface;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\Concerns\UsesAuthorization;
use Tests\Integration\AbstractDatabaseIntegrationTestCase;
use Velor\Images\Resources\ImageResource;

class ImageResourceTest extends AbstractDatabaseIntegrationTestCase
{
    use UsesAuthorization;

    public function test_the_image_index_page_renders(): void
    {
        $this->actingAsAdmin();

        $image = Image::factory()->create(['name' => 'Library image']);

        $this->get(route('images.index'))
            ->assertOk()
            ->assertSee($image->getAttribute('name'));
    }

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

    public function test_it_shows_one_selected_generated_format_at_a_time(): void
    {
        $this->app->setLocale('en');

        $image = Image::factory()->create([
            'meta_data' => [
                'formats' => [
                    'full'      => ['extension' => 'webp', 'width' => 1920],
                    'half'      => ['extension' => 'webp', 'width' => 960],
                    'preview'   => ['extension' => 'webp', 'width' => 320],
                    'thumbnail' => ['extension' => 'webp', 'width' => 150],
                ],
            ],
        ]);

        $this->setRequest([], $image);

        $resource = $this->app->make(ImageResource::class);
        $preview = $this->resolvedPreview($resource, $image);
        $properties = $this->resolvedProperties($resource, $image);
        $tabs = $resource->tabs();
        $indexPreview = $this->resolvedPreview($resource, $image, 'index');

        $this->assertStringEndsWith('/full.webp', $preview->url);
        $this->assertStringEndsWith('/thumbnail.webp', $indexPreview->url);
        $this->assertSame(1920, $properties['width']);
        $this->assertSame(['Unused', 'Full', 'Half'], array_map(
            static fn (ResourceTab $tab): string => $tab->getLabel(),
            $tabs,
        ));
        $this->assertTrue($tabs[1]->isActive(Request::create('/cms/images/' . $image->getKey())));

        $this->setRequest(['format' => 'half'], $image);

        $resource = $this->app->make(ImageResource::class);
        $preview = $this->resolvedPreview($resource, $image);
        $properties = $this->resolvedProperties($resource, $image);
        $tabs = $resource->tabs();

        $this->assertStringEndsWith('/half.webp', $preview->url);
        $this->assertSame(960, $properties['width']);
        $this->assertTrue($tabs[2]->isActive(Request::create('/cms/images/' . $image->getKey(), 'GET', [
            'format' => 'half',
        ])));
        $tabUrl = $tabs[2]->getUrl();

        $this->assertInstanceOf(Closure::class, $tabUrl);
        $this->assertSame(
            route('images.show', ['image' => $image->getKey(), 'format' => 'half']),
            $tabUrl($image),
        );
    }

    public function test_library_upload_does_not_allow_selecting_an_existing_image(): void
    {
        $field = collect($this->app->make(ImageResource::class)->fields())->first(
            static fn (Field $field): bool => $field instanceof ImageUpload,
        );

        $this->assertInstanceOf(ImageUpload::class, $field);
        $this->assertFalse($field->allowsSelectionExtensions());
        $this->assertFalse($field->collectsUsageContext());
    }

    public function test_library_upload_rejects_an_existing_image_selection(): void
    {
        $this->actingAsAdmin();

        $image = Image::factory()->create();

        $this->post(route('images.store'), [
            'upload' => [
                'present' => '1',
                'order'   => ['existing:' . $image->getKey()],
            ],
        ])->assertSessionHasErrors('upload.uploads');

        $this->assertSame(1, Image::query()->count());
    }

    public function test_the_detail_page_renders_only_the_selected_format_with_its_copyable_url(): void
    {
        $this->actingAsAdmin();

        $image = Image::factory()->create([
            'meta_data' => [
                'formats' => [
                    'full' => [
                        'extension' => 'webp',
                        'mime_type' => 'image/webp',
                        'width'     => 1920,
                        'height'    => 1080,
                    ],
                    'half' => [
                        'extension' => 'webp',
                        'mime_type' => 'image/webp',
                        'width'     => 960,
                        'height'    => 540,
                    ],
                    'preview' => [
                        'extension' => 'webp',
                        'mime_type' => 'image/webp',
                        'width'     => 320,
                        'height'    => 240,
                    ],
                    'thumbnail' => [
                        'extension' => 'webp',
                        'mime_type' => 'image/webp',
                        'width'     => 150,
                        'height'    => 150,
                    ],
                ],
            ],
        ]);

        $response = $this->get(route('images.show', [
            'image'  => $image->getKey(),
            'format' => 'half',
        ]));

        $urlGenerator = $this->app->make(ImageUrlGeneratorInterface::class);
        $halfUrl = $urlGenerator->format($image, 'half');
        $fullUrl = $urlGenerator->format($image, 'full');
        $previewUrl = $urlGenerator->format($image, 'preview');
        $thumbnailUrl = $urlGenerator->format($image, 'thumbnail');

        $response->assertOk();
        $response->assertSee('data-copy-url="' . $halfUrl . '"', false);
        $response->assertDontSee('data-copy-url="' . $fullUrl . '"', false);
        $response->assertDontSee($previewUrl, false);
        $response->assertDontSee($thumbnailUrl, false);
        $response->assertSee('Properties');
        $response->assertSee('960 px');
        $response->assertSee('540 px');

        $content = $response->getContent();

        $this->assertIsString($content);
        $this->assertSame(1, substr_count($content, 'class="cms-image-preview"'));
        $this->assertStringNotContainsString('image-format-card', $content);
    }

    /**
     * @param array<string, string> $query
     */
    protected function setRequest(array $query, ?Image $image = null): void
    {
        $request = Request::create('/cms/images', 'GET', $query);

        if ($image instanceof Image) {
            $route = new Route(['GET'], '/cms/images/{image}', []);
            $route->bind($request);
            $route->setParameter('image', $image);
            $request->setRouteResolver(static fn (): Route => $route);
        }

        $this->app->instance('request', $request);
        $this->app->instance(Request::class, $request);
    }

    protected function resolvedPreview(
        ImageResource $resource,
        Image $image,
        string $action = 'show',
    ): ImagePreviewData {
        $field = collect($resource->fields())->first(
            static fn (Field $field): bool => $field instanceof ImagePreview,
        );

        $this->assertInstanceOf(ImagePreview::class, $field);

        $preview = $field->resolveValue($image, $action);

        $this->assertInstanceOf(ImagePreviewData::class, $preview);

        return $preview;
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolvedProperties(ImageResource $resource, Image $image): array
    {
        $field = collect($resource->fields())->first(
            static fn (Field $field): bool => $field instanceof ImageMetaData,
        );

        $this->assertInstanceOf(ImageMetaData::class, $field);

        $properties = $field->resolveValue($image, 'show');

        $this->assertIsArray($properties);

        return $properties;
    }
}
