# Velor CMS Images

Velor CMS Images is the optional image management and selection layer for
Velor CMS. Velor CMS core owns image records, usages, uploads, storage,
generated formats, recovery, and regeneration.

The `2.0` release line requires Velor CMS `^1.15`. It browses and manages core
images and usages, exposes image selection for `ImageUpload` and rich text, and
groups images by their actual resource usages. It does not define its own image
models, storage services, migrations, categories, or maintenance commands.

## Development

Keep this repository outside the Velor CMS core repository. For local path
development, configure the host application to use this repository and update
the package through Composer.

The Laravel service provider is auto-discoverable.

The package declares its CMS TypeScript and Sass entrypoints through Composer.
Velor CMS discovers, builds, and loads these assets automatically; installation
does not require changes to core Blade templates or asset entrypoints.

## License

This package is open-sourced software licensed under the MIT license.
