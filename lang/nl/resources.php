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
            'title'               => 'Afbeeldingen uploaden',
            'progress'            => ':completed van :total afbeeldingen geüpload',
            'generic_error'       => 'De afbeelding kon niet worden geüpload.',
            'processing_progress' => ':completed van :total afbeeldingen verwerkt',
            'processing_error'    => 'De verwerkingsstatus kon niet worden opgehaald. De afbeeldingen worden mogelijk nog verwerkt.',
            'result_complete'     => 'Toegevoegd: :completed.',
            'result_partial'      => 'Toegevoegd: :completed. Mislukt: :failed.',
            'result_failed'       => 'Geen afbeeldingen toegevoegd. Mislukt: :failed.',
            'status_waiting'      => 'Wachten',
            'status_uploading'    => 'Uploaden',
            'status_pending'      => 'Wachten op verwerking',
            'status_processing'   => 'Verwerken',
            'status_completed'    => 'Voltooid',
            'status_failed'       => 'Mislukt',
            'view_images'         => 'Naar afbeeldingen',
        ],
        'messages' => [
            'deleted'  => 'Afbeelding verwijderd.',
            'updated'  => 'Afbeelding bijgewerkt.',
            'uploaded' => 'Afbeelding geüpload.',
        ],
    ],
];
