<?php
declare(strict_types=1);

/**
 * Logout endpoint. POST + CSRF only: a GET request must never be able to sign
 * a user out (classic logout CSRF).
 */

require __DIR__ . '/app/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Response;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    Response::redirect(Auth::check() ? admin_url() : url(''));
}

Csrf::assert();
Auth::logout();

Response::redirect(url('login.php'));
