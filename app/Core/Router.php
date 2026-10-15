<?php

namespace App\Core;

/**
 * Small routing table. Routes may carry a required permission; the router
 * refuses the request before the controller is ever constructed.
 */
final class Router
{
    /** @var array<string,array<int,array{0:string,1:string,2:string,3:?string}>> */
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $pattern, string $handler, ?string $permission = null): void
    {
        $this->add('GET', $pattern, $handler, $permission);
    }

    public function post(string $pattern, string $handler, ?string $permission = null): void
    {
        $this->add('POST', $pattern, $handler, $permission);
    }

    private function add(string $method, string $pattern, string $handler, ?string $permission): void
    {
        // "/patients/{id}/edit"  →  regex with named-ish captures
        $regex = '#^' . preg_replace('#\{[a-z_]+\}#', '([0-9]+)', rtrim($pattern, '/')) . '/?$#';
        [$controller, $action] = explode('@', $handler);
        $this->routes[$method][] = [$regex, $controller, $action, $permission];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = '/' . trim(parse_url($uri, PHP_URL_PATH) ?: '/', '/');

        foreach ($this->routes[$method] ?? [] as [$regex, $controller, $action, $permission]) {
            if (!preg_match($regex, $path, $m)) {
                continue;
            }
            array_shift($m);

            if ($permission !== null && !Auth::can($permission)) {
                if (!Auth::check()) {
                    Flash::add('error', 'Please sign in to continue.');
                    View::redirect('/login');
                }
                http_response_code(403);
                View::render('errors/403', ['title' => 'Not allowed']);
                return;
            }

            $class = "App\\Controllers\\$controller";
            (new $class())->{$action}(...array_map('intval', $m));
            return;
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Page not found']);
    }
}
