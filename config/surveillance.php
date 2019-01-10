<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | Define the username and password for authentication.
    |
     */

    'auth' => [
        'username' => env('SURVEILLANCE_USERNAME'),
        'password' => env('SURVEILLANCE_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cameras
    |--------------------------------------------------------------------------
    |
    | Define all cameras that are used for surveillance.
    |
     */

    'cameras' => [
        'front_door_01' => env('SURVEILLANCE_CAMERA01'),
    ],
];
