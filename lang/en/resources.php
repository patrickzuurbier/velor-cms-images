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
        'upload' => [
            'title'            => 'Upload progress',
            'progress'         => ':completed of :total images uploaded',
            'complete'         => 'All images have been uploaded.',
            'failed'           => 'One or more images could not be uploaded.',
            'generic_error'    => 'The image could not be uploaded.',
            'status_waiting'   => 'Waiting',
            'status_uploading' => 'Uploading',
            'status_uploaded'  => 'Uploaded',
            'status_failed'    => 'Failed',
        ],
        'messages' => [
            'deleted'  => 'Image deleted.',
            'updated'  => 'Image updated.',
            'uploaded' => 'Image uploaded.',
        ],
    ],
];
