<?php

declare(strict_types=1);

return [
    'images' => [
        'singular' => 'Image',
        'plural'   => 'Images',
        'fields'   => [
            'filename'   => 'Filename',
            'image'      => 'Image',
            'name'       => 'Name',
            'properties' => 'Properties',
        ],
        'tabs' => [
            'unused' => 'Unused',
        ],
        'picker' => [
            'close'  => 'Close',
            'empty'  => 'No images found.',
            'open'   => 'Select existing image',
            'search' => 'Search images',
            'select' => 'Select image',
            'title'  => 'Image library',
        ],
        'messages' => [
            'deleted'  => 'Image deleted.',
            'updated'  => 'Image updated.',
            'uploaded' => 'Image uploaded.',
        ],
    ],
];
