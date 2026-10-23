<?php

declare(strict_types=1);

return [
    /*
     * ------------------------------------------------------------------------
     * Default Firebase project
     * ------------------------------------------------------------------------
     */
    'default' => env('FIREBASE_PROJECT', 'app'),

    /*
     * ------------------------------------------------------------------------
     * Firebase Credentials / Service Account
     * ------------------------------------------------------------------------
     *
     * The path to your Firebase service account JSON file. This is used for
     * server-to-server authentication with Firebase APIs.
     */
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('firebase/firebase_credentials.json')),

    /*
     * ------------------------------------------------------------------------
     * Firebase project configurations
     * ------------------------------------------------------------------------
     */
    'projects' => [
        'app' => [
            /*
             * ------------------------------------------------------------------------
             * Authentication (Auth)
             * ------------------------------------------------------------------------
             *
             * Tenant ID for multi-tenancy support.
             */
            'auth' => [
                'tenant_id' => env('FIREBASE_AUTH_TENANT_ID'),
            ],
            // 'server_key' => env('FIREBASE_SERVER_KEY'),
            // 'server_key' => 'AIzaSyCvrE9ZxUHdQkQtAy2BkK62WtYm9l1Aoso',
            'project_id' => env('FIREBASE_PROJECT_ID'),
            /*
             * ------------------------------------------------------------------------
             * Firestore Component
             * ------------------------------------------------------------------------
             *
             * Define the database name here if you want to access a Firestore
             * database other than the default `(default)` database.
             */
            'firestore' => [
                // 'database' => env('FIREBASE_FIRESTORE_DATABASE'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Realtime Database
             * ------------------------------------------------------------------------
             *
             * Override the default URL of the Realtime Database if necessary.
             */
            'database' => [
                'url' => env('FIREBASE_DATABASE_URL'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Dynamic Links
             * ------------------------------------------------------------------------
             *
             * Define a default domain for new Dynamic Links created in your project.
             */
            'dynamic_links' => [
                'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Cloud Storage
             * ------------------------------------------------------------------------
             *
             * Set a default storage bucket for your application if you have
             * multiple storage buckets in Firebase.
             */
            'storage' => [
                'default_bucket' => env('FIREBASE_STORAGE_DEFAULT_BUCKET'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Caching
             * ------------------------------------------------------------------------
             *
             * Caching data returned by the Firebase API, such as Google's public
             * keys for verifying ID tokens.
             */
            'cache_store' => env('FIREBASE_CACHE_STORE', 'file'),

            /*
             * ------------------------------------------------------------------------
             * Logging
             * ------------------------------------------------------------------------
             *
             * Configure logging for HTTP interactions with Firebase APIs.
             */
            'logging' => [
                'http_log_channel' => env('FIREBASE_HTTP_LOG_CHANNEL', 'stack'),
                'http_debug_log_channel' => env('FIREBASE_HTTP_DEBUG_LOG_CHANNEL', 'stack'),
            ],

            /*
             * ------------------------------------------------------------------------
             * HTTP Client Options
             * ------------------------------------------------------------------------
             *
             * Customize the HTTP client's behavior for API requests.
             */
            'http_client_options' => [
                'proxy' => env('FIREBASE_HTTP_CLIENT_PROXY'),
                'timeout' => env('FIREBASE_HTTP_CLIENT_TIMEOUT', 30),
                'guzzle_middlewares' => [],
            ],
        ],
    ],
];
