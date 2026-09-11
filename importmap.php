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
    'frixtur' => [
        'path' => './assets/frixtur.js',
        'entrypoint' => true,
    ],
    'weshtavu' => [
        'path' => './assets/weshtavu.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    'bootstrap' => [
        'version' => '5.3.8',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.8',
        'type' => 'css',
    ],
    'immutable' => [
        'version' => '5.1.5',
    ],
    'bootstrap-icons/font/bootstrap-icons' => [
        'version' => '1.13.1',
    ],
    'tom-select' => [
        'version' => '2.5.2',
    ],
    'datatables.net' => [
        'version' => '2.3.7',
    ],
    'jquery' => [
        'version' => '4.0.0',
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
        'version' => '4.4.3',
    ],
    'load-script' => [
        'version' => '2.0.0',
    ],
    'ms' => [
        'version' => '2.1.3',
    ],
    'magnific-popup' => [
        'version' => '1.2.0',
    ],
    'magnific-popup/dist/magnific-popup.min.css' => [
        'version' => '1.2.0',
        'type' => 'css',
    ],
    'bootstrap-icons/font/bootstrap-icons.css' => [
        'version' => '1.13.1',
        'type' => 'css',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.23',
    ],
    'tom-select/dist/css/tom-select.css' => [
        'version' => '2.5.2',
        'type' => 'css',
    ],
    'datatables.net-dt' => [
        'version' => '2.3.7',
    ],
    'datatables.net-dt/css/dataTables.dataTables.min.css' => [
        'version' => '2.3.7',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.default.css' => [
        'version' => '2.5.2',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.bootstrap4.css' => [
        'version' => '2.5.2',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.bootstrap5.css' => [
        'version' => '2.5.2',
        'type' => 'css',
    ],
    'masonry-layout' => [
        'version' => '4.2.2',
    ],
    'outlayer' => [
        'version' => '2.1.1',
    ],
    'get-size' => [
        'version' => '3.0.0',
    ],
    'ev-emitter' => [
        'version' => '2.1.2',
    ],
    'fizzy-ui-utils' => [
        'version' => '3.0.0',
    ],
    'desandro-matches-selector' => [
        'version' => '2.0.2',
    ],
    '@orchidjs/sifter' => [
        'version' => '1.1.0',
    ],
    '@orchidjs/unicode-variants' => [
        'version' => '1.1.2',
    ],
    'tom-select/dist/css/tom-select.default.min.css' => [
        'version' => '2.5.2',
        'type' => 'css',
    ],
];
