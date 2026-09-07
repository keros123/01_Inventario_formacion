<?php

class App
{
    public static function baseUrl(): string
    {
        if (PHP_SAPI !== 'cli' && isset($_SERVER['SCRIPT_NAME'])) {
            return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        }

        return '';
    }
}
