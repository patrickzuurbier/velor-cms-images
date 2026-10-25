# Changelog

All notable changes to Velor CMS Images will be documented in this file.

This package follows semantic versioning: `MAJOR.MINOR.PATCH`.

## [Unreleased]

## [2.0.0-rc.7] - 2026-10-25

### Changed

- Started image library uploads immediately after file selection and replaced
  the resource form actions with an inline per-file progress and result flow.
- Kept successfully processed images accessible after partial batch failures
  through an explicit link to the Unused image overview.

### Fixed

- Prevented large upload result lists from overflowing an inaccessible fixed
  progress dialog.

## [2.0.0-rc.6] - 2026-10-23

### Fixed

- Centered the image upload progress dialog within the viewport.

## [2.0.0-rc.5] - 2026-10-19

### Added

- Added multi-image library uploads with one isolated request per image,
  limited concurrent uploads, aggregate upload progress, and per-file feedback.
- Added queued format-processing progress with chunked status polling and a
  responsive progress interface for large batches.

### Changed

- Limited image picker results to images whose configured formats have been
  processed successfully.
- Raised the minimum Velor CMS version to `^1.19` for configurable image upload
  limits, queued format generation, processing statuses, and the resource
  upload button.

## [2.0.0-rc.4] - 2026-10-13

### Changed

- Raised the minimum PHP version to `8.4.1` to match Velor CMS core.

## [2.0.0-rc.3] - 2026-10-13

### Changed

- Limited the package picker to selecting existing images for core
  `ImageUpload` fields and removed its former rich text and direct-upload
  integration.
- Changed picker cards to use the core `preview` format and image index rows to
  use `thumbnail`.
- Hid the internal `preview` and `thumbnail` formats from image detail tabs
  while retaining responsive selected-format previews, properties, and URL
  copying.
- Raised the minimum Velor CMS version to `^1.17` for contextual image metadata,
  resource usage cleanup, and the revised display formats.

## [2.0.0-rc.2] - 2026-10-12

### Added

- Added package-provided `imagePicker()` configuration to core `ImageUpload`
  fields so existing-image selection can be disabled per field.
- Added direct image uploads to the shared picker when it is opened from a rich
  text editor.

### Changed

- Changed image detail pages to show one responsive preview for the selected
  format with intrinsic dimensions, format properties, and a copyable URL.
- Raised the minimum Velor CMS version to `^1.16` for the latest image field,
  preview, format, and rich text usage integration.

### Fixed

- Fixed image index rendering by requiring the core image sorting support added
  in Velor CMS `1.15.2`.
- Limited the image library create form to new uploads instead of offering
  selection of an image that is already stored.

## [2.0.0-rc.1] - 2026-10-10

### Added

- Added an image management resource with usage-based resource tabs, an Unused
  tab, uploads, name editing, and guarded single and bulk deletion.
- Added one package-owned image picker for existing-image selection in core
  `ImageUpload` fields and rich text editors, including image search, generated
  format selection, contextual alt text, and translated controls.
- Added one-at-a-time generated format previews with format metadata and
  copyable URLs to image detail pages.
- Declared package TypeScript and Sass entrypoints for automatic discovery by
  the Velor CMS Vite build.

### Changed

- Rebuilt the package against Velor CMS `^1.15` as an optional management and
  selection layer on top of core images and usages.
- Removed package-owned image models, migrations, categories, repositories,
  storage and format services, maintenance commands, routes, views, and legacy
  tests now owned or replaced by Velor CMS core.
- Reduced the package to an auto-discoverable service provider before rebuilding
  the image management and selection layer on core images and usages.

## [1.5.3] - 2026-09-21

### Fixed

- Required GD and added an explicit WebP capability check before generated
  image formats are replaced.

## [1.5.2] - 2026-09-07

### Changed

- Moved image and image category queries into package repositories.

## [1.5.1] - 2026-09-03

### Fixed

- Fixed resource validation requests to pass resource instances to Velor CMS
  validation factories.

## [1.5.0] - 2026-08-31

### Changed

- Updated controllers and resource registration for the Velor CMS `^1.9`
  resource-first contract.

## [1.4.0] - 2026-08-22

### Changed

- Removed package config toggles for resources and policies; installed image
  packages now register their own resources, policies, routes, and menu items.
- Consolidated image schema changes into initial package migrations.
- Switched image order forms to the Velor CMS `Order` field and scoped image row
  ordering by image category.
- Removed rollback methods from package migrations.
- Updated factories to use compact row order defaults and `$this->faker`.

## [1.3.0] - 2026-08-13

### Changed

- Updated the package to use Velor CMS `^1.8` CMS menu registration contracts.

## [1.2.1] - 2026-08-04

### Added

- Added `velor:images:recover` to recover missing image database rows from
  originals that still exist in S3 storage.

### Fixed

- Fixed drag row ordering for uncategorized images on the Common tab.

## [1.2.0] - 2026-08-03

### Changed

- Replaced the package-specific image row ordering endpoint with Velor CMS core
  row ordering.
- Raised the Velor CMS core requirement to `^1.6`.
- Documented local package development outside the Velor CMS core repository.

## [1.1.2] - 2026-07-23

### Changed

- Documented that installing the package enables the Velor CMS image picker for
  core rich text fields through the `images.picker` route.

## [1.1.1] - 2026-07-22

### Changed

- Moved image preview, format preview, and row reordering behavior onto Velor
  CMS resource extension hooks so core no longer needs image-specific runtime
  references.
- Clarified package installation documentation for VCS installs, local path
  development, migrations, and seeders.

## [1.1.0] - 2026-07-22

### Added

- Added translatable image descriptions for localized rich text image alt
  defaults.

### Changed

- Documented package migration and seeder setup for consuming applications.

## [1.0.0] - 2026-07-22

### Added

- Initial local package extraction for the Image and Image Category resource
  slice.
- Package-owned models, factories, migrations, seeders, controllers, requests,
  resources, policies, translations, CMS menu registration, CMS routes, image
  services, and regeneration command.

### Changed

- Removed the unused legacy image category config; image categories are managed
  as database records.

[Unreleased]: https://github.com/patrickzuurbier/velor-cms-images/compare/2.0.0-rc.7...HEAD
[2.0.0-rc.7]: https://github.com/patrickzuurbier/velor-cms-images/compare/2.0.0-rc.6...2.0.0-rc.7
[2.0.0-rc.6]: https://github.com/patrickzuurbier/velor-cms-images/compare/2.0.0-rc.5...2.0.0-rc.6
[2.0.0-rc.5]: https://github.com/patrickzuurbier/velor-cms-images/compare/2.0.0-rc.4...2.0.0-rc.5
[2.0.0-rc.4]: https://github.com/patrickzuurbier/velor-cms-images/compare/2.0.0-rc.3...2.0.0-rc.4
[2.0.0-rc.3]: https://github.com/patrickzuurbier/velor-cms-images/compare/2.0.0-rc.2...2.0.0-rc.3
[2.0.0-rc.2]: https://github.com/patrickzuurbier/velor-cms-images/compare/2.0.0-rc.1...2.0.0-rc.2
[2.0.0-rc.1]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.5.3...2.0.0-rc.1
[1.5.3]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.5.2...1.5.3
[1.5.2]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.5.1...1.5.2
[1.5.1]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.5.0...1.5.1
[1.5.0]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.4.0...1.5.0
[1.4.0]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.3.0...1.4.0
[1.3.0]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.2.1...1.3.0
[1.2.1]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.1.2...1.2.0
[1.1.2]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/patrickzuurbier/velor-cms-images/releases/tag/1.0.0
