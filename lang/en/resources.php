<?php

declare(strict_types=1);

return [
    'images' => [
        'singular' => 'Image',
        'plural'   => 'Images',
        'fields'   => [
            'filename'          => 'Filename',
            'image'             => 'Image',
            'name'              => 'Name',
            'original_metadata' => 'Original metadata',
        ],
        'tabs' => [
            'unused' => 'Unused',
        ],
        'picker' => [
            'alt'        => 'Alternative text',
            'close'      => 'Close',
            'empty'      => 'No images found.',
            'format'     => 'Image format',
            'no_formats' => 'No public image formats are available.',
            'open'       => 'Select existing image',
            'search'     => 'Search images',
            'select'     => 'Select image',
            'title'      => 'Image library',
        ],
        'messages' => [
            'deleted'  => 'Image deleted.',
            'updated'  => 'Image updated.',
            'uploaded' => 'Image uploaded.',
        ],
    ],
];
