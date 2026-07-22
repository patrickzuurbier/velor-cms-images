<?php

declare(strict_types=1);

return [
    'image-categories' => [
        'singular' => 'Afbeeldingscategorie',
        'plural'   => 'Afbeeldingscategorieen',
        'fields'   => [
            'name' => 'Naam',
        ],
    ],
    'images' => [
        'singular' => 'Afbeelding',
        'plural'   => 'Afbeeldingen',
        'fields'   => [
            'category'          => 'Categorie',
            'description'       => 'Omschrijving',
            'filename'          => 'Bestandsnaam',
            'formats'           => 'Formaten',
            'image'             => 'Afbeelding',
            'name'              => 'Naam',
            'order'             => 'Volgorde',
            'original_metadata' => 'Originele metadata',
        ],
        'metadata' => [
            'extension' => 'Extensie',
            'height'    => 'Hoogte',
            'mime_type' => 'Mime-type',
            'size'      => 'Grootte',
            'width'     => 'Breedte',
        ],
        'help' => [
            'description' => 'Wordt gebruikt als standaard alt-tekst wanneer je deze afbeelding in rich text plaatst.',
        ],
        'tabs' => [
            'all'    => 'Alles',
            'common' => 'Algemeen',
        ],
    ],
];
