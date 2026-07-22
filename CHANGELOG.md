# Changelog

All notable changes to Velor CMS Images will be documented in this file.

This package follows semantic versioning: `MAJOR.MINOR.PATCH`.

## [Unreleased]

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
  resources, policies, translations, sidebar registration, CMS routes, image
  services, and regeneration command.

### Changed

- Removed the unused legacy image category config; image categories are managed
  as database records.

[Unreleased]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.1.0...HEAD
[1.1.0]: https://github.com/patrickzuurbier/velor-cms-images/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/patrickzuurbier/velor-cms-images/releases/tag/1.0.0
