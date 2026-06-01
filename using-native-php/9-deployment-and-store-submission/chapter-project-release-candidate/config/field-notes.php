<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Biometric app lock
    |--------------------------------------------------------------------------
    |
    | When enabled, note routes require Face ID / Touch ID / fingerprint (or
    | device PIN via OS fallback) before content is shown. Disable for local
    | browser dev; enable on simulator/device builds.
    |
    */

    'lock_enabled' => env('FIELD_NOTES_LOCK_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Store listing
    |--------------------------------------------------------------------------
    */

    'privacy_policy_url' => env('FIELD_NOTES_PRIVACY_POLICY_URL'),

];
