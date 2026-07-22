<?php

declare(strict_types=1);

use Velor\Images\Models\Image;
use Velor\Images\Models\ImageCategory;
use Velor\Images\Policies\ImageCategoryPolicy;
use Velor\Images\Policies\ImagePolicy;
use Velor\Images\Resources\ImageCategoryResource;
use Velor\Images\Resources\ImageResource;

return [
    'enabled' => true,

    'resources' => [
        'image_category' => ImageCategoryResource::class,
        'image'          => ImageResource::class,
    ],

    'policies' => [
        ImageCategory::class => ImageCategoryPolicy::class,
        Image::class         => ImagePolicy::class,
    ],

    'max_size' => 10240,

    'categories' => [
        'news',
        'paragraph',
        'slide',
    ],

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
