<?php
/**
 * Aesthetica — front controller.
 * Every request enters here.
 */

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Database;
use App\Core\Router;
use App\Core\View;

require __DIR__ . '/../app/Core/View.php';
require __DIR__ . '/../app/helpers.php';

spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, 4));
    foreach ([__DIR__ . "/../app/$relative.php", __DIR__ . '/../app/Core/Support.php'] as $file) {
        if (is_file($file)) {
            require_once $file;
            if (class_exists($class, false)) {
                return;
            }
        }
    }
});

$config = require __DIR__ . '/../config/config.php';

date_default_timezone_set($config['app']['timezone']);
ini_set('display_errors', $config['app']['debug'] ? '1' : '0');
error_reporting(E_ALL);

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

try {
    Database::connect($config['db']);
} catch (Throwable $e) {
    http_response_code(500);
    exit('<h1>Database connection failed</h1><p>Check config/config.php. '
        . ($config['app']['debug'] ? htmlspecialchars($e->getMessage()) : '') . '</p>');
}

// Work out the base path so the app runs in a sub-folder too.
$base = $config['app']['base_url']
    ?: rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($base === '/') {
    $base = '';
}
View::setBase($base);

$uri = $_SERVER['REQUEST_URI'] ?? '/';
if ($base !== '' && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base));
}

$GLOBALS['config'] = $config;

$router = new Router();
require __DIR__ . '/../app/routes.php';

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $uri ?: '/');
} catch (Throwable $e) {
    http_response_code(500);
    if ($config['app']['debug']) {
        echo '<pre style="padding:20px;font:13px/1.6 monospace">'
            . htmlspecialchars($e->getMessage() . "\n\n" . $e->getTraceAsString()) . '</pre>';
    } else {
        View::render('errors/500', ['title' => 'Something went wrong']);
    }
}
