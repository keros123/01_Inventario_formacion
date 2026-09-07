<?php

class Controller
{
    protected array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/app.php';
    }

    protected function view(string $view, array $data = [], ?string $layout = 'main'): void
    {
        extract($data);
        $config = $this->config;
        $contentView = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($contentView)) {
            http_response_code(404);
            echo 'Vista no encontrada.';
            return;
        }

        ob_start();
        require $contentView;
        $content = ob_get_clean();

        if ($layout) {
            require __DIR__ . '/../views/layouts/' . $layout . '.php';
        } else {
            echo $content;
        }
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $this->config['base_url'] . $path);
        exit;
    }

    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function setFlash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function getFlash(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}
