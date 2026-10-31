<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    // Normalize only validated string input in AccountDetails / LoginRequest.
    'lowercase_usernames' => false,
    'home' => '/dashboard',
    'prefix' => '',
    'domain' => null,
    'middleware' => ['web'],
    'auth_middleware' => 'auth',
    'views' => true,
    'limiters' => ['login' => 'login'],
    // Enable only the features supported by this batch's actions and UI.
    // Fortify provides password confirmation independently of this list.
    'features' => [Features::registration()],
];
