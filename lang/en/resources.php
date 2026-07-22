<?php

declare(strict_types=1);

return [
    'image-categories' => [
        'singular' => 'Image category',
        'plural'   => 'Image categories',
        'fields'   => [
            'name' => 'Name',
        ],
    ],
    'images' => [
        'singular' => 'Image',
        'plural'   => 'Images',
        'fields'   => [
            'category'          => 'Category',
            'description'       => 'Description',
            'filename'          => 'Filename',
            'formats'           => 'Formats',
            'image'             => 'Image',
            'name'              => 'Name',
            'order'             => 'Order',
            'original_metadata' => 'Original metadata',
        ],
        'metadata' => [
            'extension' => 'Extension',
            'height'    => 'Height',
            'mime_type' => 'Mime type',
            'size'      => 'Size',
            'width'     => 'Width',
        ],
        'help' => [
            'description' => 'Used as the default alt text when inserting this image into rich text.',
        ],
        'tabs' => [
            'all'    => 'All',
            'common' => 'Common',
        ],
    ],
];
