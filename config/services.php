<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tankstellensuche beim Anlegen einer Station
    |--------------------------------------------------------------------------
    |
    | Die PLZ wird per Nominatim geocodiert. Overpass sucht anschließend nach
    | OSM-Objekten mit `amenity=fuel`. Alle URLs bleiben austauschbar, damit in
    | einem größeren Produktivbetrieb eigene Instanzen nutzbar sind.
    |
    */
    'station_geocoder' => [
        'url' => env('STATION_GEOCODER_URL', 'https://nominatim.openstreetmap.org/search'),
    ],

    'overpass' => [
        'url' => env('OVERPASS_API_URL', 'https://overpass-api.de/api/interpreter'),
    ],

    'openstreetmap' => [
        'user_agent' => env('OPENSTREETMAP_USER_AGENT', 'StationDesk/1.0 (+'.env('APP_URL', 'http://localhost').')'),
    ],

];
