<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\View;

final class AuthController
{
    // deliberately does not extend Controller — these routes are public
    public function __construct() {}

    public function showLogin(): void
    {
        if (Auth::check()) {
            View::redirect('/dashboard');
        }
        View::render('auth/login', ['title' => 'Sign in', 'layout' => 'layouts/plain']);
    }

    public function login(): void
    {
        Csrf::verify();

        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            Flash::add('error', 'Enter your email and password.');
            View::redirect('/login');
        }

        if (!Auth::attempt($email, $password)) {
            Flash::add('error', 'Those details did not match our records.');
            View::redirect('/login');
        }

        Flash::add('success', 'Welcome back, ' . Auth::user()['full_name'] . '.');
        View::redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        View::redirect('/login');
    }
}
