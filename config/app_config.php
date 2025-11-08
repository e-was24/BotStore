<?php
return [
    'app_name' => 'BotStore',
    'debug' => true,
    'allowed_origins' => ['localhost', '127.0.0.1', 'botstore.infinityfreeapp.com'], // ganti sesuai domain kamu
    'token_ttl' => 3600,
    'log_path' => __DIR__ . '/../logs/access.log',
    'storage_path' => __DIR__ . '/../storage/',
    'downloads_path' => __DIR__ . '/../storage/downloads/',
];
