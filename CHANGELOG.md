# Changelog

All notable changes to Velor CMS Images will be documented in this file.

This package follows semantic versioning: `MAJOR.MINOR.PATCH`.

## [Unreleased]

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

[Unreleased]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.5.0...HEAD
[1.5.0]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.4.0...1.5.0
[1.4.0]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.3.0...1.4.0
[1.3.0]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.2.1...1.3.0
[1.2.1]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.1.2...1.2.0
[1.1.2]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/patrickzuurbier/velor-cms-images/releases/tag/1.0.0
