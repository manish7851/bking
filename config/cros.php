<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin requests may execute
    | JS code in your browser. You have a lot of flexibility here and
    | can fine-tune your CORS policies to fit your application.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', '*'], // Apply CORS to these paths, adjust as needed
    'allowed_methods' => ['*'], // Allow all methods (GET, POST, PUT, DELETE, etc.)
    'allowed_origins' => ['http://localhost:54098'], // Your Flutter app's origin
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'], // Allow all headers
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,

];
