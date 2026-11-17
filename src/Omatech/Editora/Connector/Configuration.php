<?php

return [
    'database_timezone' => env('EDITORA_DATABASE_TIMEZONE', 'Europe/Madrid'),
    'routeParams' => [
        [
            'language'
        ],
        [
            'language',
            'nice_url'
        ]
        /*,[
            'language',
            '::blog', Static Route
            'nice_url'
        ]*/
    ],
    'homeNiceUrl'        => false,
    'availableLanguages' => ['ca', 'es'],
    'defaultLanguage'    => 'ca',
    'ignoreBrowserLanguage' => false,
    'forcedLanguage'     => '',
    'adminAlias'         => 'admin',
    'middlewares'        => [],
    'extractNullValues'  => false
];
