<?php

namespace App\Core;

/**
 * Session authentication and permission checks.
 * Permissions are loaded once per login and cached in the session.
 */
final class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $user = Database::one(
            'SELECT u.*, r.slug AS role_slug, r.name AS role_name
               FROM users u JOIN roles r ON r.id = u.role_id
              WHERE u.email = ? AND u.is_active = 1',
            [$email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        $perms = Database::all(
            'SELECT p.slug FROM permissions p
               JOIN role_permissions rp ON rp.permission_id = p.id
              WHERE rp.role_id = ?',
            [$user['role_id']]
        );

        unset($user['password_hash']);
        $_SESSION['user']        = $user;
        $_SESSION['permissions'] = array_column($perms, 'slug');

        $prac = Database::one('SELECT id FROM practitioners WHERE user_id = ?', [$user['id']]);
        $_SESSION['practitioner_id'] = $prac['id'] ?? null;

        Database::update('users', (int) $user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);
        Audit::log('login', 'user', (int) $user['id']);

        return true;
    }

    public static function logout(): void
    {
        Audit::log('logout', 'user', self::id() ?? 0);
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user']) ? (int) $_SESSION['user']['id'] : null;
    }

    public static function practitionerId(): ?int
    {
        return $_SESSION['practitioner_id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role_slug'] ?? null;
    }

    public static function can(string $permission): bool
    {
        return in_array($permission, $_SESSION['permissions'] ?? [], true);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Flash::add('error', 'Please sign in to continue.');
            View::redirect('/login');
        }
    }
}
