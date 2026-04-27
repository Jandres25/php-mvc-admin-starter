<?php

namespace App\Middleware;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
            if (!checkSessionTimeout() || !checkSessionSecurity()) {
                $_SESSION['message'] = 'Session expired due to inactivity. Please log in again.';
                $_SESSION['icon']    = 'warning';
            }
            header('Location: ' . URL . 'login');
            exit;
        }

        refreshPermissionsIfStale();
    }
}
