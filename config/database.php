<?php

require_once __DIR__ . '/env.php';

return [
    'url'             => env('SUPABASE_URL', 'https://dvybtelxkptnvclbqpwa.supabase.co'),
    'anon_key'        => env('SUPABASE_ANON_KEY', 'sb_publishable_Ztn9j5bz3PRD2s1cW1JA1w_CUOI6uyy'),
    'storage_bucket'  => env('SUPABASE_STORAGE_BUCKET', 'productos'),
    'prefix'          => env('DB_PREFIX', 'inv_form_'),
];
