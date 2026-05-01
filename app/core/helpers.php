<?php

/**
 * Global helper functions for session, CSRF, and auth checks.
 * Loaded once from public/index.php.
 */

if (!function_exists('isAuthenticated')) {
    function isAuthenticated(): bool
    {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }
}

if (!function_exists('checkSessionTimeout')) {
    function checkSessionTimeout(?int $timeout = null): bool
    {
        if ($timeout === null) {
            $timeout = (int) env('SESSION_LIFETIME', 1800);
            if ($timeout <= 0) { $timeout = 1800; }
        }
        if (isset($_SESSION['last_access'])) {
            if (time() - $_SESSION['last_access'] >= $timeout) {
                session_unset();
                session_destroy();
                return false;
            }
        }
        $_SESSION['last_access'] = time();
        return true;
    }
}

if (!function_exists('tryAutoLoginFromRememberCookie')) {
    function tryAutoLoginFromRememberCookie(): bool
    {
        if (isAuthenticated()) { return true; }
        $svc = new \App\Services\RememberMeService();
        return $svc->attemptLogin();
    }
}

if (!function_exists('checkSessionSecurity')) {
    function checkSessionSecurity(): bool
    {
        if (isset($_SESSION['ip'], $_SESSION['user_agent'])) {
            if (
                $_SESSION['ip'] !== $_SERVER['REMOTE_ADDR'] ||
                $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']
            ) {
                session_unset();
                session_destroy();
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('getCurrentUser')) {
    function getCurrentUser(): ?array
    {
        if (isAuthenticated()) {
            return [
                'id'       => $_SESSION['user_id']      ?? null,
                'name'     => $_SESSION['user_name']     ?? null,
                'email'    => $_SESSION['user_email']    ?? null,
                'position' => $_SESSION['user_position'] ?? null,
                'image'    => $_SESSION['user_image']    ?? 'public/img/user_default.jpg',
            ];
        }
        return null;
    }
}

if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('regenerateCSRFToken')) {
    function regenerateCSRFToken(): string
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('refreshPermissionsIfStale')) {
    function refreshPermissionsIfStale(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId || !isset($_SESSION['user_permissions'])) {
            return;
        }

        $userModel   = new \App\Models\User();
        $dbTimestamp = $userModel->getPermissionsTimestamp($userId);
        $sessionTs   = $_SESSION['permissions_ts'] ?? null;

        if ($dbTimestamp && (!$sessionTs || $dbTimestamp > $sessionTs)) {
            $authService = new \App\Services\AuthorizationService();
            $permissions = $authService->getUserPermissions($userId);
            $_SESSION['user_permissions'] = array_column($permissions, 'name');
            $_SESSION['permissions_ts']   = $dbTimestamp;
        }
    }
}
