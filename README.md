<div align="center">
    <img src="https://velorstack.nl/images/velor-logo.png" alt="Velor" width="88">
    <h1>Velor CMS Images</h1>
</div>

Velor CMS Images is the optional image-management and selection layer for
Velor. It adds an image library, usage-based scopes, generated-format
presentation, and existing-image selection to Velor's core image capability.

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Core And Package Ownership](#core-and-package-ownership)
- [Package Registration](#package-registration)
- [Routes](#routes)
- [Image Selection](#image-selection)
- [Image Library Uploads](#image-library-uploads)
- [Resource Scopes And Usages](#resource-scopes-and-usages)
- [Generated Formats](#generated-formats)
- [Repository And Extension Contracts](#repository-and-extension-contracts)
- [Authorization](#authorization)
- [Asset Integration](#asset-integration)
- [Translations](#translations)
- [Testing](#testing)
- [Uninstalling](#uninstalling)
- [License](#license)

## Requirements

- PHP `^8.4.1`
- Velor `^1.26`

## Installation

Install the package in a Velor application:

```bash
composer require patrickzuurbier/velor-cms-images:^2.2
npm run build
```

Laravel package discovery loads `ImagesServiceProvider`. The package does not
own migrations or require a package configuration file.

## Core And Package Ownership

Velor core owns image records, usages, uploads, storage, generated formats,
processing, recovery, regeneration, and physical deletion. This package uses
those core models and services rather than duplicating them.

The package owns:

- the Images management resource and authenticated routes;
- the registered `ImagePolicy` and Velor menu item;
- image-library upload and processing-status interfaces;
- usage-derived index scopes;
- generated-format presentation;
- the existing-image picker for core `ImageUpload` fields;
- package translations, views, TypeScript, and Sass.

Removing the package does not remove core image records, usages, generated
formats, or stored files.

## Package Registration

The service provider:

- registers the Images resource and its policy;
- registers the Images menu item, which is appended unless `velor-menu.php`
  assigns it a fixed grouping and position;
- loads authenticated routes through Velor's route registrar;
- loads package translations and views;
- binds the package repository, presentation factories, and resolvers;
- extends Velor's bulk-delete service with safe physical image deletion;
- adds the `imagePicker()` option to the core `ImageUpload` field.

## Routes

Package routes use the application-configured Velor prefix and authentication
middleware. The resource routes use the `images.*` names. Two focused JSON
endpoints support package interfaces:

- `GET images/picker` (`images.picker`) returns selectable completed images;
- `POST images/processing-statuses` (`images.processing-statuses`) returns
  processing states for requested image IDs.

Both endpoints require image viewing privileges.

## Image Selection

Installing this package adds an image picker to the core `ImageUpload` field.
The picker lets a developer's resource select an image uploaded earlier while
retaining the field's normal upload support.

Image selection is enabled by default. Disable it for an individual field when
only a new upload is valid:

```php
use App\Resources\Fields\ImageUpload;

ImageUpload::make('image')
    ->imagePicker(false);
```

The `imagePicker()` option is package-provided and is unavailable when this
package is not installed. The Images library's own create form disables the
picker because that flow is specifically for new uploads.

Only images whose core processing status is `completed` are returned by the
picker.

## Image Library Uploads

The Images resource Upload action accepts multiple new images. Every image is
uploaded independently, so validation or transport failure for one file does
not block the remaining batch. The package starts uploads after file selection,
limits concurrent requests, and tracks core format processing separately.

Partial failures retain successfully processed images. Completed images without
usages appear in the Unused scope.

The maximum file size applies per image and follows Velor's core
`MAX_IMAGE_SIZE` configuration. The package does not introduce a second upload
limit or storage configuration.

## Resource Scopes And Usages

The Images index derives one scope from each `resource_type` present in core
image usages. An image used by multiple resource types appears in every matching
scope. Images without usages appear in the Unused scope.

Scope URLs use either `scope=resource:{resource_type}` or `scope=unused`.
Unknown scopes safely fall back to the first available resource scope or to
Unused when no resource usages exist.

Only unused images may be deleted physically. The same rule applies to regular,
force, and bulk deletion. Removing a used image from the library is denied even
when the role has the corresponding delete privilege.

## Generated Formats

The image show view presents generated formats as tabs. Selecting a format
shows its URL, extension, MIME type, file size, width, and height. The internal
`preview` and `thumbnail` formats remain available to Velor interfaces but are
not exposed as selectable format tabs.

Format definitions, generation, metadata, URLs, and storage remain core-owned.

## Repository And Extension Contracts

`ImageManagementRepositoryInterface` is the package's management query and
persistence boundary. It provides:

- `resourceTypes()` for distinct usage resource types;
- `indexQuery()` for unused or resource-scoped images;
- `pickerImages()` for completed selectable images;
- `update()` for image management attributes;
- `isUnused()` for deletion authorization.

The provider also binds focused contracts for format data and presentation,
picker data, index tabs, resource field values, and resource-scope resolution
below `Velor\Images\Factories\Contracts` and
`Velor\Images\Resolvers\Contracts`. Applications that deliberately replace
one of these presentation boundaries should rebind its contract from an
application service provider after package registration.

Core storage and upload services must not be replaced with package-local
implementations.

## Authorization

The registered `ImagePolicy` resolves Velor role privileges for standard image
resource abilities. Velor's resource middleware authorizes standard controller
actions before they run. Picker and processing-status endpoints authorize
`viewAny` explicitly, and bulk deletion authorizes every selected image.

Delete and force-delete authorization additionally require the image to have no
usages. Hidden menu items and controls are not the security boundary.

## Asset Integration

The package declares `resources/js/velor.ts` and
`resources/sass/velor.scss` through Composer's Velor Vite metadata. Velor
discovers, builds, and loads these assets without package-specific changes to
core Blade templates or asset entrypoints.

Rebuild host assets after installing or updating the package:

```bash
npm run build
```

## Translations

Translations load under the `velor-images` namespace. Publish them only when
the consuming application needs project-owned overrides:

```bash
php artisan vendor:publish --tag=velor-images-lang
```

Published translations are written to `lang/vendor/velor-images`.

## Testing

Package tests live in `tests` and run against a consuming Velor test
application. They cover package registration, authorization, image management
queries, resource scopes, generated-format presentation, image selection,
uploads, and safe bulk deletion.

## Uninstalling

Remove the package with Composer and rebuild the remaining application assets:

```bash
composer remove patrickzuurbier/velor-cms-images
npm run build
```

The Images resource, picker, package routes, policy, menu item, views,
translations, and frontend extensions disappear. Core images, usages, physical
files, and generated formats remain intact. Published translations remain
project-owned and are not removed automatically.

## License

This package is open-sourced software licensed under the MIT license.
