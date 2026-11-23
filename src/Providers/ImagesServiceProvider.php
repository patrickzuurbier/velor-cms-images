<?php

declare(strict_types=1);

namespace Velor\Images\Providers;

use App\Models\Image;
use App\Repositories\Contracts\ImageRepositoryInterface;
use App\Resources\Fields\ImageUpload;
use App\Services\Authorization\Contracts\PolicyRegistryInterface;
use App\Services\Images\Contracts\ImageUploadServiceInterface;
use App\Services\Resources\Contracts\ResourceBulkDeleteServiceInterface;
use App\Services\Resources\Contracts\ResourceRegistryInterface;
use App\Services\VelorMenu\Contracts\VelorMenuItemRegistryInterface;
use App\Services\VelorMenu\Data\VelorMenuItemData;
use App\Services\VelorRouting\Contracts\VelorRouteRegistrarInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Velor\Images\Factories\Contracts\ImageFormatDataFactoryInterface;
use Velor\Images\Factories\Contracts\ImageFormatPresentationFactoryInterface;
use Velor\Images\Factories\Contracts\ImagePickerDataFactoryInterface;
use Velor\Images\Factories\Contracts\ImageResourceIndexTabFactoryInterface;
use Velor\Images\Factories\ImageFormatDataFactory;
use Velor\Images\Factories\ImageFormatPresentationFactory;
use Velor\Images\Factories\ImagePickerDataFactory;
use Velor\Images\Factories\ImageResourceIndexTabFactory;
use Velor\Images\Policies\ImagePolicy;
use Velor\Images\Repositories\Contracts\ImageManagementRepositoryInterface;
use Velor\Images\Repositories\ImageManagementRepository;
use Velor\Images\Resources\ImageResource;
use Velor\Images\Resolvers\Contracts\ImageResourceFieldValueResolverInterface;
use Velor\Images\Resolvers\Contracts\ImageResourceScopeResolverInterface;
use Velor\Images\Resolvers\ImageResourceFieldValueResolver;
use Velor\Images\Resolvers\ImageResourceScopeResolver;
use Velor\Images\Services\ImageResourceBulkDeleteService;

class ImagesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        ImageUpload::macro('imagePicker', function (bool $enabled = true): ImageUpload {
            /** @var ImageUpload $this */
            return $this->allowSelectionExtensions($enabled);
        });

        $this->app->singleton(ImageManagementRepositoryInterface::class, ImageManagementRepository::class);
        $this->app->singleton(ImageFormatDataFactoryInterface::class, ImageFormatDataFactory::class);
        $this->app->singleton(ImageFormatPresentationFactoryInterface::class, ImageFormatPresentationFactory::class);
        $this->app->singleton(ImagePickerDataFactoryInterface::class, ImagePickerDataFactory::class);
        $this->app->singleton(ImageResourceIndexTabFactoryInterface::class, ImageResourceIndexTabFactory::class);
        $this->app->singleton(
            ImageResourceFieldValueResolverInterface::class,
            ImageResourceFieldValueResolver::class,
        );
        $this->app->singleton(ImageResourceScopeResolverInterface::class, ImageResourceScopeResolver::class);
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
        VelorRouteRegistrarInterface $velorRoutes,
        ResourceRegistryInterface $resources,
        PolicyRegistryInterface $policies,
        VelorMenuItemRegistryInterface $velorMenuItems,
    ): void {
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'velor-images');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'velor-images');

        $resources->register($this->app->make(ImageResource::class));
        $policies->register(Image::class, ImagePolicy::class);

        $velorMenuItems->registerBefore(
            'users.index',
            new VelorMenuItemData(
                Image::class,
                'images.index',
                'velor-images::resources.images.plural',
                'bi-images',
            ),
        );

        $velorRoutes->loadAuthenticated(__DIR__ . '/../../routes/velor.php');

        $this->publishes([
            __DIR__ . '/../../lang' => $this->app->langPath('vendor/velor-images'),
        ], 'velor-images-lang');
    }
}
