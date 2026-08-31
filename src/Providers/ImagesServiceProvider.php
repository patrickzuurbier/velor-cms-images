<?php

declare(strict_types=1);

namespace Velor\Images\Providers;

use App\Services\Authorization\Contracts\PolicyRegistryInterface;
use App\Services\CmsMenu\Contracts\CmsMenuItemRegistryInterface;
use App\Services\CmsMenu\Data\CmsMenuItemData;
use App\Services\CmsRouting\Contracts\CmsRouteRegistrarInterface;
use App\Services\Resources\Contracts\ResourceRegistryInterface;
use Illuminate\Support\ServiceProvider;
use Velor\Images\Console\Commands\RecoverImagesCommand;
use Velor\Images\Console\Commands\RegenerateImagesCommand;
use Velor\Images\Models\Image;
use Velor\Images\Models\ImageCategory;
use Velor\Images\Policies\ImageCategoryPolicy;
use Velor\Images\Policies\ImagePolicy;
use Velor\Images\Resources\ImageCategoryResource;
use Velor\Images\Resources\ImageResource;
use Velor\Images\Services\Contracts\ImageDisplayFormatResolverInterface;
use Velor\Images\Services\Contracts\ImageFormatDataFactoryInterface;
use Velor\Images\Services\Contracts\ImageFormatGeneratorInterface;
use Velor\Images\Services\Contracts\ImageOrderServiceInterface;
use Velor\Images\Services\Contracts\ImagePathGeneratorInterface;
use Velor\Images\Services\Contracts\ImagePickerDataFactoryInterface;
use Velor\Images\Services\Contracts\ImageRecoveryServiceInterface;
use Velor\Images\Services\Contracts\ImageRegenerationServiceInterface;
use Velor\Images\Services\Contracts\ImageUploadServiceInterface;
use Velor\Images\Services\Contracts\ImageUrlGeneratorInterface;
use Velor\Images\Services\ImageDisplayFormatResolver;
use Velor\Images\Services\ImageFormatDataFactory;
use Velor\Images\Services\ImageFormatGenerator;
use Velor\Images\Services\ImageOrderService;
use Velor\Images\Services\ImagePathGenerator;
use Velor\Images\Services\ImagePickerDataFactory;
use Velor\Images\Services\ImageRecoveryService;
use Velor\Images\Services\ImageRegenerationService;
use Velor\Images\Services\ImageUploadService;
use Velor\Images\Services\ImageUrlGenerator;

class ImagesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/velor-images.php', 'velor-images');

        $this->app->singleton(
            ImagePathGeneratorInterface::class,
            ImagePathGenerator::class,
        );

        $this->app->singleton(
            ImageUrlGeneratorInterface::class,
            ImageUrlGenerator::class,
        );

        $this->app->singleton(
            ImageFormatGeneratorInterface::class,
            ImageFormatGenerator::class,
        );

        $this->app->singleton(
            ImageFormatDataFactoryInterface::class,
            ImageFormatDataFactory::class,
        );

        $this->app->singleton(
            ImageDisplayFormatResolverInterface::class,
            ImageDisplayFormatResolver::class,
        );

        $this->app->singleton(
            ImageUploadServiceInterface::class,
            ImageUploadService::class,
        );

        $this->app->singleton(
            ImageRegenerationServiceInterface::class,
            ImageRegenerationService::class,
        );

        $this->app->singleton(
            ImageOrderServiceInterface::class,
            ImageOrderService::class,
        );

        $this->app->singleton(
            ImageRecoveryServiceInterface::class,
            ImageRecoveryService::class,
        );

        $this->app->singleton(
            ImagePickerDataFactoryInterface::class,
            ImagePickerDataFactory::class,
        );
    }

    public function boot(
        CmsRouteRegistrarInterface $cmsRoutes,
        ResourceRegistryInterface $resources,
        PolicyRegistryInterface $policies,
        CmsMenuItemRegistryInterface $cmsMenuItems,
    ): void {
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'velor-images');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'velor-images');

        if ($this->app->runningInConsole()) {
            $this->commands([
                RecoverImagesCommand::class,
                RegenerateImagesCommand::class,
            ]);
        }

        $resources->register($this->app->make(ImageCategoryResource::class));
        $resources->register($this->app->make(ImageResource::class));

        $policies->register(ImageCategory::class, ImageCategoryPolicy::class);
        $policies->register(Image::class, ImagePolicy::class);

        $cmsMenuItems->registerBefore(
            'navigations.index',
            new CmsMenuItemData(Image::class, 'images.index', 'velor-images::resources.images.plural', 'bi-images'),
        );
        $cmsMenuItems->registerBefore(
            'navigations.index',
            new CmsMenuItemData(ImageCategory::class, 'image-categories.index', 'velor-images::resources.image-categories.plural', 'bi-tags'),
        );

        $cmsRoutes->loadAuthenticated(__DIR__.'/../../routes/cms.php');

        $this->publishes([
            __DIR__.'/../../config/velor-images.php' => $this->app->configPath('velor-images.php'),
        ], 'velor-images-config');

        $this->publishes([
            __DIR__.'/../../database/migrations' => $this->app->databasePath('migrations'),
        ], 'velor-images-migrations');

        $this->publishes([
            __DIR__.'/../../database/seeders' => $this->app->databasePath('seeders'),
        ], 'velor-images-seeders');

        $this->publishes([
            __DIR__.'/../../lang' => $this->app->langPath('vendor/velor-images'),
        ], 'velor-images-lang');

        $this->publishes([
            __DIR__.'/../../resources/views' => $this->app->resourcePath('views/vendor/velor-images'),
        ], 'velor-images-views');
    }
}
