<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Wert Private Key (Legacy - Backward Compatible)
    |--------------------------------------------------------------------------
    |
    | This is your private key provided by Wert during registration.
    | You can also set this using the WERT_PRIVATE_KEY environment variable.
    | This is maintained for backward compatibility.
    |
    */
    'private_key' => env('WERT_PRIVATE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Multiple Credentials
    |--------------------------------------------------------------------------
    |
    | Define multiple named credentials for use in the same application.
    | Each credential should be a valid Ed25519 private key (64 hex characters).
    |
    | Example:
    | 'credentials' => [
    |     'default' => env('WERT_PRIVATE_KEY'),
    |     'production' => env('WERT_PRIVATE_KEY_PRODUCTION'),
    |     'staging' => env('WERT_PRIVATE_KEY_STAGING'),
    |     'partner_a' => env('WERT_PRIVATE_KEY_PARTNER_A'),
    | ],
    |
    */
    'credentials' => [
        'default' => env('WERT_PRIVATE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Credential
    |--------------------------------------------------------------------------
    |
    | The name of the credential to use when none is explicitly specified.
    | This should match a key in the 'credentials' array above.
    |
    */
    'default_credential' => env('WERT_DEFAULT_CREDENTIAL', 'default'),
];