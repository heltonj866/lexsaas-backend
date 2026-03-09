<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    
    // Substituímos o '*' pelo endereço exato do seu React para permitir as credenciais
    'allowed_origins' => ['http://localhost:5173', 'http://127.0.0.1:5173'],
    
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    
    // 👇 A MÁGICA ACONTECE AQUI 👇
    'supports_credentials' => true,
];