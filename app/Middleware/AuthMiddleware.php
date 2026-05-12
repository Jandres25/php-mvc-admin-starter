<?php

namespace App\Middleware;

use App\Core\Auth;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (!Auth::check()) {
            if (Auth::attemptRememberLogin()) {
                return;
            }

            $_SESSION['message'] = 'Session expired due to inactivity. Please log in again.';
            $_SESSION['icon']    = 'warning';
            header('Location: ' . URL . 'login');
            exit;
        }

        if (!Auth::checkTimeout() || !Auth::checkSecurity()) {
            if (!Auth::attemptRememberLogin()) {
                $_SESSION['message'] = 'Session expired due to inactivity. Please log in again.';
                $_SESSION['icon']    = 'warning';
                header('Location: ' . URL . 'login');
                exit;
            }
        }

        Auth::refreshPermissionsIfStale();
    }
}
