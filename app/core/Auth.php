<?php

class Auth
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(array $user): void
    {
        self::startSession();
        $_SESSION['user'] = [
            'cedula'  => $user['Cedula'],
            'nombres' => $user['Nombres'],
            'tipo'    => $user['Tipo'],
        ];
    }

    public static function logout(): void
    {
        self::startSession();
        session_destroy();
    }

    public static function check(): bool
    {
        self::startSession();
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        self::startSession();
        return $_SESSION['user'] ?? null;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && $user['tipo'] === 'Admin';
    }

    public static function requireLogin(): void
    {
        $config = require __DIR__ . '/../../config/app.php';
        if (!self::check()) {
            header('Location: ' . $config['base_url'] . '/login');
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        $config = require __DIR__ . '/../../config/app.php';
        if (!self::isAdmin()) {
            header('Location: ' . $config['base_url'] . '/dashboard');
            exit;
        }
    }
}
