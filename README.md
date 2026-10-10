# Velor CMS Images

Velor CMS Images is the optional image management and selection layer for
Velor CMS. Velor CMS core owns image records, usages, uploads, storage,
generated formats, recovery, and regeneration.

The package is being rebuilt for `2.0.0` against Velor CMS `^1.15`. The rebuilt
package will browse and manage core images and usages, expose image selection
for `ImageUpload` and rich text, and group images by their actual resource
usages. It will not define its own image models, storage services, migrations,
categories, or maintenance commands.

## Development

Keep this repository outside the Velor CMS core repository. For local path
development, configure the host application to use this repository and update
the package through Composer.

The Laravel service provider remains auto-discoverable while the `2.0.0`
feature set is rebuilt.

The package declares its CMS TypeScript and Sass entrypoints through Composer.
Velor CMS discovers, builds, and loads these assets automatically; installation
does not require changes to core Blade templates or asset entrypoints.

## License

This package is open-sourced software licensed under the MIT license.
