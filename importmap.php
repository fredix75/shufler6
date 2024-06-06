<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    'bootstrap' => [
        'version' => '5.3.3',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.3',
        'type' => 'css',
    ],
    'immutable' => [
        'version' => '4.3.6',
    ],
    'bootstrap-icons/font/bootstrap-icons' => [
        'version' => '1.11.3',
    ],
    'tom-select' => [
        'version' => '2.3.1',
    ],
    'datatables.net' => [
        'version' => '2.0.8',
    ],
    'jquery' => [
        'version' => '3.7.1',
    ],
    'stimulus-autocomplete' => [
        'version' => '3.1.0',
    ],
    'youtube-player' => [
        'version' => '5.6.0',
    ],
    'sister' => [
        'version' => '3.0.2',
    ],
    'debug' => [
        'version' => '4.3.5',
    ],
    'load-script' => [
        'version' => '1.0.0',
    ],
    'ms' => [
        'version' => '2.1.2',
    ],
    'magnific-popup' => [
        'version' => '1.1.0',
    ],
    'magnific-popup/dist/magnific-popup.min.css' => [
        'version' => '1.1.0',
        'type' => 'css',
    ],
    'bootstrap-icons/font/bootstrap-icons.css' => [
        'version' => '1.11.3',
        'type' => 'css',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'tom-select/dist/css/tom-select.css' => [
        'version' => '2.3.1',
        'type' => 'css',
    ],
    'datatables.net-dt' => [
        'version' => '2.0.8',
    ],
    'datatables.net-dt/css/dataTables.dataTables.min.css' => [
        'version' => '2.0.8',
        'type' => 'css',
    ],
];
