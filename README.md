# Velor CMS Images

Images is a first-party resource plugin for Velor CMS. It owns image uploads,
image categories, generated formats, the CMS image picker endpoint, image
ordering, image format regeneration, and the image resource views.

## Installation

Install the package in a Velor CMS application:

```bash
composer config repositories.velor-cms-images vcs https://github.com/patrickzuurbier/velor-cms-images.git
composer require patrickzuurbier/velor-cms-images:^1.1
```

For local path development inside the Velor CMS repository, temporarily point
Composer to the package workspace and update the package from the app
container:

```bash
composer config repositories.velor-cms-images path packages/velor/images
docker compose exec app composer update patrickzuurbier/velor-cms-images --with-dependencies
```

With a local path repository, Composer can symlink
`vendor/patrickzuurbier/velor-cms-images` to `packages/velor/images`. Remove the
local path repository again before testing a real install from GitHub:

```bash
composer config --unset repositories.velor-cms-images
composer config repositories.velor-cms-images vcs https://github.com/patrickzuurbier/velor-cms-images.git
```

The package service provider is auto-discovered by Laravel. When enabled, it
registers resources, policies, sidebar items, CMS routes, translations,
migrations, views, services, and the image regeneration command.

Publish the package migrations when the application should own them:

```bash
php artisan vendor:publish --tag=velor-images-migrations
```

Then run the database migrations:

```bash
php artisan migrate
```

For local development, use the Makefile from the Velor CMS host application:

```bash
make migrate
```

Optionally publish and run the image category seeder for local data:

```bash
php artisan vendor:publish --tag=velor-images-seeders
php artisan db:seed --class=Velor\Images\Database\Seeders\ImageCategoriesTableSeeder
```

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
