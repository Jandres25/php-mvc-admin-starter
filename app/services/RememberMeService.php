<?php

namespace App\Services;

use App\Models\User;

class RememberMeService
{
    private User $users;
    private string $cookieName;
    private int $lifetime;

    public function __construct()
    {
        $this->users      = new User();
        $this->cookieName = (string) env('REMEMBER_ME_COOKIE_NAME', 'remember_me');
        $this->lifetime   = (int) env('REMEMBER_ME_LIFETIME', 2592000);
        if ($this->lifetime <= 0) { $this->lifetime = 2592000; }
    }

    public function issue(int $userId): void
    {
        $token     = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expires   = date('Y-m-d H:i:s', time() + $this->lifetime);

        $this->users->setRememberToken($userId, $tokenHash, $expires);
        $this->setCookie($token, time() + $this->lifetime);
    }

    public function attemptLogin(): bool
    {
        if (empty($_COOKIE[$this->cookieName])) { return false; }

        $token = (string) $_COOKIE[$this->cookieName];
        if (strlen($token) !== 64) { $this->clearCookie(); return false; }

        $tokenHash = hash('sha256', $token);
        $user      = $this->users->findByRememberToken($tokenHash);

        if (!$user) { $this->clearCookie(); return false; }

        session_regenerate_id(true);
        $_SESSION['user_id']       = $user['id'];
        $_SESSION['user_name']     = $user['name'] . ' ' . $user['first_surname'];
        $_SESSION['user_email']    = $user['email'];
        $_SESSION['user_position'] = $user['position'];
        $_SESSION['user_image']    = $user['image'] ?? 'user_default.jpg';
        $_SESSION['authenticated'] = true;
        $_SESSION['last_access']   = time();
        $_SESSION['ip']            = $_SERVER['REMOTE_ADDR']     ?? '';
        $_SESSION['user_agent']    = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $auth = new AuthorizationService();
        if (strtolower((string) $user['position']) === 'administrator') {
            $_SESSION['user_permissions'] = ['*'];
        } else {
            $perms = $auth->getUserPermissions((int) $user['id']);
            $_SESSION['user_permissions'] = array_column($perms, 'name');
        }
        $_SESSION['permissions_ts'] = date('Y-m-d H:i:s');

        // Rotate token on each use to mitigate cookie theft
        $newToken     = bin2hex(random_bytes(32));
        $newTokenHash = hash('sha256', $newToken);
        $newExpires   = date('Y-m-d H:i:s', time() + $this->lifetime);
        $this->users->setRememberToken((int) $user['id'], $newTokenHash, $newExpires);
        $this->setCookie($newToken, time() + $this->lifetime);

        return true;
    }

    public function clear(int $userId): void
    {
        $this->users->clearRememberToken($userId);
        $this->clearCookie();
    }

    private function setCookie(string $value, int $expires): void
    {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $path   = defined('APP_BASE_PATH') ? (APP_BASE_PATH === '' ? '/' : APP_BASE_PATH) : '/';
        setcookie($this->cookieName, $value, [
            'expires'  => $expires,
            'path'     => $path,
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private function clearCookie(): void
    {
        $path = defined('APP_BASE_PATH') ? (APP_BASE_PATH === '' ? '/' : APP_BASE_PATH) : '/';
        setcookie($this->cookieName, '', [
            'expires'  => time() - 3600,
            'path'     => $path,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        unset($_COOKIE[$this->cookieName]);
    }
}
