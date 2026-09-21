# Velor CMS Images

Images is a first-party resource plugin for Velor CMS. It owns image uploads,
image categories, generated formats, the CMS image picker endpoint, image
ordering, image format regeneration, and the image resource views.

## Contents

- [Installation](#installation)
- [Package Contents](#package-contents)
- [Configuration](#configuration)
- [Image Commands](#image-commands)
- [Rich Text Image Picker](#rich-text-image-picker)
- [Routes](#routes)
- [Publishing](#publishing)
- [Testing](#testing)
- [Uninstalling](#uninstalling)
- [License](#license)

## Installation

Install the package in a Velor CMS application:

```bash
composer config repositories.velor-cms-images vcs https://github.com/patrickzuurbier/velor-cms-images.git
composer require patrickzuurbier/velor-cms-images:^1.5
```

This package requires Velor CMS `^1.9` and PHP GD compiled with WebP support.

For local path development, keep the package repository outside the Velor CMS
core repository. Temporarily point Composer to the package workspace and update
the package from the app container:

```bash
composer config repositories.velor-cms-images path ../velor-cms-images
docker compose exec app composer update patrickzuurbier/velor-cms-images --with-dependencies
```

With a local path repository, Composer can symlink
`vendor/patrickzuurbier/velor-cms-images` to `../velor-cms-images`. Remove the
local path repository again before testing a real install from GitHub:

```bash
composer config --unset repositories.velor-cms-images
composer config repositories.velor-cms-images vcs https://github.com/patrickzuurbier/velor-cms-images.git
```

The package service provider is auto-discovered by Laravel. When installed, it
registers resources, policies, CMS menu items, CMS routes, translations,
migrations, views, services, and the image regeneration command.

Publish the package migrations when the application should own them:

```bash
php artisan vendor:publish --tag=velor-images-migrations
```

Then run the database migrations:

```bash
php artisan migrate
```

Optionally publish and run the image category seeder for local data:

```bash
php artisan vendor:publish --tag=velor-images-seeders
php artisan db:seed --class='Velor\Images\Database\Seeders\ImageCategoriesTableSeeder'
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
src/Console/Commands/RecoverImagesCommand.php
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

## Image Commands

Regenerate all configured formats from the stored originals:

```bash
php artisan velor:images:regenerate
```

Recover missing image database rows from originals that still exist in S3:

```bash
php artisan velor:images:recover
```

Use a dry run to inspect recoverable images without inserting records:

```bash
php artisan velor:images:recover --dry-run
```

Recovery is useful after a database refresh when the S3 `media` bucket still
contains uploaded files. It recreates one image row per
`images/{image-id}/original.*` object and restores available metadata from the
stored files. The original upload filename cannot be recovered from S3, so the
recovered filename is the stored original object name.

If image formats are missing or the configured formats changed, run recovery
first and then regenerate formats:

```bash
php artisan velor:images:recover
php artisan velor:images:regenerate
```

## Rich Text Image Picker

Installing this package adds the CMS image picker endpoint:

```text
images.picker
```

Velor CMS core detects that route while building `RichText` form inputs. When
the route is available, the rich text editor replaces Quill's default image
button with the Velor CMS image picker, so users can insert managed images and
choose generated formats.

Without this package, `RichText` keeps Quill's original URL-based image
insertion. Users can still paste an image URL manually. With this package
installed, users can also copy generated image format URLs from the image
resource and paste them into Quill's original image URL prompt in contexts
where the Velor picker is not used.

Per-field picker control may be added to the core `RichText` field later. For
now, picker activation is automatic and based on the `images.picker` route.

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
CMS menu items, translations, services, commands, and loaded migrations disappear
with the package. Existing database tables, uploaded files, and published files
are project data and are not deleted automatically by Composer.

## License

This package is open-sourced software licensed under the MIT license.
