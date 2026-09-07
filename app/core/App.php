<?php

class App
{
    public static function baseUrl(): string
    {
        $configured = getenv('APP_BASE_URL');
        if ($configured === false) {
            $configured = $_ENV['APP_BASE_URL'] ?? $_SERVER['APP_BASE_URL'] ?? '';
        }
        if (is_string($configured) && $configured !== '') {
            return rtrim(str_replace('\\', '/', $configured), '/');
        }

        if (PHP_SAPI !== 'cli' && isset($_SERVER['SCRIPT_NAME'])) {
            return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        }

        return '';
    }
}
