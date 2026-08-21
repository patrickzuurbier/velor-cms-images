<?php

declare(strict_types=1);

return [
    'max_size' => 10240,

    'mimes' => [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
    ],

    'formats' => [
        'full' => [
            'width'   => 1920,
            'height'  => null,
            'fit'     => 'contain',
            'format'  => 'webp',
            'quality' => 85,
            'upscale' => false,
        ],
        'half' => [
            'width'   => 960,
            'height'  => null,
            'fit'     => 'contain',
            'format'  => 'webp',
            'quality' => 85,
            'upscale' => false,
        ],
        'cms' => [
            'width'   => 960,
            'height'  => 640,
            'fit'     => 'contain',
            'format'  => 'webp',
            'quality' => 85,
            'upscale' => false,
        ],
        'thumbnail' => [
            'width'   => 320,
            'height'  => 240,
            'fit'     => 'cover',
            'format'  => 'webp',
            'quality' => 80,
            'upscale' => false,
        ],
    ],

    'picker' => [
        'default_format' => 'half',
        'formats'        => [
            'half',
            'full',
        ],
    ],
];
