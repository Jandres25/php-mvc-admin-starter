<?php

namespace App\Middleware;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
            if (tryAutoLoginFromRememberCookie()) {
                $_SESSION['last_access'] = time();
                refreshPermissionsIfStale();
                return;
            }

            if (!checkSessionTimeout() || !checkSessionSecurity()) {
                $_SESSION['message'] = 'Session expired due to inactivity. Please log in again.';
                $_SESSION['icon']    = 'warning';
            }
            header('Location: ' . URL . 'login');
            exit;
        }

        if (!checkSessionTimeout() || !checkSessionSecurity()) {
            if (!tryAutoLoginFromRememberCookie()) {
                $_SESSION['message'] = 'Session expired due to inactivity. Please log in again.';
                $_SESSION['icon']    = 'warning';
                header('Location: ' . URL . 'login');
                exit;
            }
        }

        refreshPermissionsIfStale();
    }
}
