<?php

namespace App\Core;

final class View
{
    private static string $base = '';

    public static function setBase(string $base): void
    {
        self::$base = rtrim($base, '/');
    }

    public static function url(string $path = '/'): string
    {
        return self::$base . '/' . ltrim($path, '/');
    }

    /** Render a view inside the main layout. */
    public static function render(string $template, array $data = []): void
    {
        $content = self::capture($template, $data);
        $data['content'] = $content;
        $layout = $data['layout'] ?? 'layouts/app';
        extract($data, EXTR_SKIP);
        require __DIR__ . "/../Views/$layout.php";
    }

    /** Render a view without a layout (partials, print pages). */
    public static function capture(string $template, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . "/../Views/$template.php";
        return (string) ob_get_clean();
    }

    public static function redirect(string $path): never
    {
        header('Location: ' . self::url($path));
        exit;
    }

    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}
