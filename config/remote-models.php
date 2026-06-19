<?php


return [


    'domain' =>
    env(
        'REMOTE_DOMAIN',
        'http://127.0.0.1:8001'
    ),



    'api-path' =>
    '/api/remote/models',



    'api-key' =>
    env(
        'REMOTE_MODELS_API_KEY',
        'your-api-key-here'
    ),

];
