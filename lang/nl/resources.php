<?php

declare(strict_types=1);

return [
    'images' => [
        'singular' => 'Afbeelding',
        'plural'   => 'Afbeeldingen',
        'fields'   => [
            'filename'   => 'Bestandsnaam',
            'image'      => 'Afbeelding',
            'name'       => 'Naam',
            'properties' => 'Eigenschappen',
        ],
        'tabs' => [
            'unused' => 'Ongebruikt',
        ],
        'picker' => [
            'close'  => 'Sluiten',
            'empty'  => 'Geen afbeeldingen gevonden.',
            'open'   => 'Bestaande afbeelding selecteren',
            'search' => 'Afbeeldingen zoeken',
            'select' => 'Afbeelding selecteren',
            'title'  => 'Afbeeldingenbibliotheek',
        ],
        'upload' => [
            'title'            => 'Uploadvoortgang',
            'progress'         => ':completed van :total afbeeldingen geüpload',
            'complete'         => 'Alle afbeeldingen zijn geüpload.',
            'failed'           => 'Een of meer afbeeldingen konden niet worden geüpload.',
            'generic_error'    => 'De afbeelding kon niet worden geüpload.',
            'status_waiting'   => 'Wachten',
            'status_uploading' => 'Uploaden',
            'status_uploaded'  => 'Geüpload',
            'status_failed'    => 'Mislukt',
        ],
        'messages' => [
            'deleted'  => 'Afbeelding verwijderd.',
            'updated'  => 'Afbeelding bijgewerkt.',
            'uploaded' => 'Afbeelding geüpload.',
        ],
    ],
];
