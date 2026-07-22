# Velor CMS Images

Images is a first-party resource plugin for Velor CMS. It owns image uploads,
image categories, generated formats, the CMS image picker endpoint, image
ordering, image format regeneration, and the image resource views.

## Installation

Install the package in a Velor CMS application:

```bash
composer require patrickzuurbier/velor-cms-images
```

For local path development inside the Velor CMS repository, keep the path
repository in the host `composer.json` and update the package from the app
container:

```bash
docker compose exec app composer update patrickzuurbier/velor-cms-images --with-dependencies
```

The package service provider is auto-discovered by Laravel. When enabled, it
registers resources, policies, sidebar items, CMS routes, translations,
migrations, views, services, and the image regeneration command.

## Package Contents

```text
src/Models/Image.php
src/Models/ImageCategory.php
src/Resources/ImageResource.php
src/Resources/ImageCategoryResource.php
src/Http/Controllers
src/Http/Requests
src/Policies
src/Services
src/Console/Commands/RegenerateImagesCommand.php
database/migrations
database/seeders
lang/en/resources.php
lang/nl/resources.php
routes/cms.php
config/velor-images.php
resources/views
tests
```

## Configuration

Image configuration lives in:

```text
config/velor-images.php
```

It defines allowed mime types, maximum upload size, generated formats, picker
formats, and the default picker format.

## Routes

CMS routes live in `routes/cms.php`. The service provider loads that file
through Velor CMS' route registrar, so this package does not define the global
`cms` prefix or CMS middleware itself.

## Publishing

The package should remain in `vendor` by default. Publish only project-owned
files:

```bash
php artisan vendor:publish --tag=velor-images-config
php artisan vendor:publish --tag=velor-images-migrations
php artisan vendor:publish --tag=velor-images-seeders
php artisan vendor:publish --tag=velor-images-lang
php artisan vendor:publish --tag=velor-images-views
```

## Testing

Package integration is covered by the Velor CMS host application test suite:

```bash
make test
```

The package-level `tests` namespace is reserved for standalone package tests.

## Uninstalling

The package can be removed from a consuming application with Composer:

```bash
composer remove patrickzuurbier/velor-cms-images
```

Velor CMS core should continue to boot without this package once no image
resources are configured. Package-owned CMS routes, resources, policies,
sidebar items, translations, services, commands, and loaded migrations disappear
with the package. Existing database tables, uploaded files, and published files
are project data and are not deleted automatically by Composer.

## License

This package is open-sourced software licensed under the MIT license.
