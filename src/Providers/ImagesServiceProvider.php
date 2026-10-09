<?php

declare(strict_types=1);

namespace Velor\Images\Providers;

use App\Models\Image;
use App\Repositories\Contracts\ImageRepositoryInterface;
use App\Services\Authorization\Contracts\PolicyRegistryInterface;
use App\Services\CmsMenu\Contracts\CmsMenuItemRegistryInterface;
use App\Services\CmsMenu\Data\CmsMenuItemData;
use App\Services\CmsRouting\Contracts\CmsRouteRegistrarInterface;
use App\Services\Images\Contracts\ImageUploadServiceInterface;
use App\Services\Resources\Contracts\ResourceBulkDeleteServiceInterface;
use App\Services\Resources\Contracts\ResourceRegistryInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Velor\Images\Policies\ImagePolicy;
use Velor\Images\Repositories\Contracts\ImageManagementRepositoryInterface;
use Velor\Images\Repositories\ImageManagementRepository;
use Velor\Images\Resources\ImageResource;
use Velor\Images\Services\ImageResourceBulkDeleteService;

class ImagesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ImageManagementRepositoryInterface::class, ImageManagementRepository::class);
        $this->app->extend(
            ResourceBulkDeleteServiceInterface::class,
            fn (ResourceBulkDeleteServiceInterface $service, Application $app): ResourceBulkDeleteServiceInterface => new ImageResourceBulkDeleteService(
                $app->make(Request::class),
                $service,
                $app->make(ImageUploadServiceInterface::class),
                $app->make(ImageRepositoryInterface::class),
            ),
        );
    }

    public function boot(
        CmsRouteRegistrarInterface $cmsRoutes,
        ResourceRegistryInterface $resources,
        PolicyRegistryInterface $policies,
        CmsMenuItemRegistryInterface $cmsMenuItems,
    ): void {
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'velor-images');

        $resources->register($this->app->make(ImageResource::class));
        $policies->register(Image::class, ImagePolicy::class);

        $cmsMenuItems->registerBefore(
            'users.index',
            new CmsMenuItemData(
                Image::class,
                'images.index',
                'velor-images::resources.images.plural',
                'bi-images',
            ),
        );

        $cmsRoutes->loadAuthenticated(__DIR__ . '/../../routes/cms.php');

        $this->publishes([
            __DIR__ . '/../../lang' => $this->app->langPath('vendor/velor-images'),
        ], 'velor-images-lang');
    }
}
