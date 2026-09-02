<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This option determines where compiled Blade views are stored. On Vercel
    | (read-only filesystem), this MUST point to /tmp — the only writable
    | location. Override via VIEW_COMPILED_PATH env var.
    |
    */

    'compiled' => env('VIEW_COMPILED_PATH', realpath(storage_path('framework/views'))),

];
