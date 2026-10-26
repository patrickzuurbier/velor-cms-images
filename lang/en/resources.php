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
            'title'  => 'Image library',
        ],
        'upload' => [
            'back'                => 'Back',
            'title'               => 'Upload images',
            'progress'            => ':completed of :total images uploaded',
            'generic_error'       => 'The image could not be uploaded.',
            'processing_progress' => ':completed of :total images processed',
            'processing_error'    => 'The processing status could not be retrieved. The images may still be processing.',
            'result_complete'     => 'Added: :completed.',
            'result_partial'      => 'Added: :completed. Failed: :failed.',
            'result_failed'       => 'No images were added. Failed: :failed.',
            'status_waiting'      => 'Waiting',
            'status_uploading'    => 'Uploading',
            'status_pending'      => 'Waiting for processing',
            'status_processing'   => 'Processing',
            'status_completed'    => 'Completed',
            'status_failed'       => 'Failed',
            'view_images'         => 'View images',
        ],
        'messages' => [
            'deleted'  => 'Image deleted.',
            'updated'  => 'Image updated.',
            'uploaded' => 'Image uploaded.',
        ],
    ],
];
