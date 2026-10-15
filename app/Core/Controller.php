<?php

namespace App\Core;

abstract class Controller
{
    public function __construct()
    {
        Auth::requireLogin();
    }

    protected function view(string $template, array $data = []): void
    {
        View::render($template, $data);
    }

    protected function redirect(string $path): never
    {
        View::redirect($path);
    }

    /** Trimmed POST value. */
    protected function input(string $key, ?string $default = null): ?string
    {
        $v = $_POST[$key] ?? $default;
        return is_string($v) ? trim($v) : $default;
    }

    protected function intInput(string $key, ?int $default = null): ?int
    {
        $v = $_POST[$key] ?? null;
        return ($v === null || $v === '') ? $default : (int) $v;
    }

    protected function floatInput(string $key, float $default = 0.0): float
    {
        $v = $_POST[$key] ?? null;
        return ($v === null || $v === '') ? $default : (float) $v;
    }

    protected function query(string $key, ?string $default = null): ?string
    {
        $v = $_GET[$key] ?? $default;
        return is_string($v) ? trim($v) : $default;
    }

    protected function validate(array $rules, array $labels = []): Validator
    {
        return (new Validator($_POST))->check($rules, $labels);
    }

    protected function notFound(string $what = 'Record'): never
    {
        http_response_code(404);
        View::render('errors/404', ['title' => 'Not found', 'message' => "$what not found."]);
        exit;
    }
}
