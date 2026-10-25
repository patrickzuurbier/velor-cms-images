# Velor CMS Images

Velor CMS Images is the optional image management and selection layer for
Velor CMS. Velor CMS core owns image records, usages, uploads, storage,
generated formats, recovery, and regeneration.

The `2.0` release line requires PHP `^8.4.1` and Velor CMS `^1.19.4`. It browses
and manages core images and usages, exposes image selection for `ImageUpload`,
and groups images by their actual resource usages. It does not define its own
image models, storage services, migrations, categories, or maintenance commands.

## Image selection

Installing this package adds an image picker to the core `ImageUpload` field.
The picker lets editors select an image that was uploaded earlier, while the
field keeps its normal upload support.

Image selection is enabled by default. Disable it for an individual field when
only a new upload is valid:

```php
use App\Resources\Fields\ImageUpload;

ImageUpload::make('image')
    ->imagePicker(false);
```

The `imagePicker()` option is provided by this package and is unavailable when
the package is not installed.

## Image library uploads

The Images resource Upload action accepts multiple new images. Every image is
uploaded independently, so validation or transport failure for one file does
not block the remaining batch. The package starts uploads immediately after
file selection, limits concurrent requests, and tracks core format processing
separately. Partial failures retain successfully processed images and expose
the completed results through the Unused image scope.

The maximum file size applies per image and follows the core `MAX_IMAGE_SIZE`
configuration. Only fully processed images are available in the image picker.

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
