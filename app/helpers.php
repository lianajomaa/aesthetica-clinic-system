<?php
/**
 * Global template helpers. Loaded once from the front controller.
 */

use App\Core\Database as DB;
use App\Core\View;

if (!function_exists('e')) {
    /** Escape for HTML output. Used on every variable printed in a template. */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        return View::url($path);
    }
}

if (!function_exists('money')) {
    function money(float|string|null $amount, bool $symbol = true): string
    {
        $v = number_format((float) $amount, 2);
        return $symbol ? setting('currency_symbol', '$') . $v : $v;
    }
}

if (!function_exists('qty')) {
    /** Quantities print without trailing zeros: 24 units, 1.5 ml. */
    function qty(float|string|null $n): string
    {
        $f = (float) $n;
        return rtrim(rtrim(number_format($f, 2, '.', ''), '0'), '.') ?: '0';
    }
}

if (!function_exists('dt')) {
    function dt(?string $value, string $format = 'j M Y, H:i'): string
    {
        return $value ? date($format, strtotime($value)) : '—';
    }
}

if (!function_exists('d')) {
    function d(?string $value, string $format = 'j M Y'): string
    {
        return $value ? date($format, strtotime($value)) : '—';
    }
}

if (!function_exists('t')) {
    function t(?string $value): string
    {
        return $value ? date('H:i', strtotime($value)) : '—';
    }
}

if (!function_exists('setting')) {
    function setting(string $key, string $default = ''): string
    {
        static $cache = null;
        if ($cache === null) {
            try {
                $cache = array_column(DB::all('SELECT `key`, `value` FROM settings'), 'value', 'key');
            } catch (Throwable) {
                $cache = [];
            }
        }
        return $cache[$key] ?? $default;
    }
}

if (!function_exists('statusClass')) {
    function statusClass(string $status): string
    {
        return match ($status) {
            'completed' => 'ok',
            'in_room'   => 'live',
            'arrived'   => 'live',
            'confirmed' => 'info',
            'booked'    => 'muted',
            'no_show'   => 'bad',
            'cancelled' => 'bad',
            'paid'      => 'ok',
            'partial'   => 'warn',
            'unpaid'    => 'bad',
            'void'      => 'muted',
            'pending'   => 'warn',
            'done'      => 'ok',
            'dismissed' => 'muted',
            'mild'      => 'info',
            'moderate'  => 'warn',
            'severe'    => 'bad',
            default     => 'muted',
        };
    }
}

if (!function_exists('label')) {
    function label(string $value): string
    {
        return ucfirst(str_replace('_', ' ', $value));
    }
}

if (!function_exists('initials')) {
    function initials(string $name): string
    {
        $clean = trim(str_replace(['Dr.', 'Dr'], '', $name));
        $parts = preg_split('/\s+/', $clean) ?: [];
        return strtoupper(mb_substr($parts[0] ?? '?', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $fallback = ''): string
    {
        return e($_POST[$key] ?? $fallback);
    }
}
