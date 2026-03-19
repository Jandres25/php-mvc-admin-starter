<?php

/**
 * Session Management
 *
 * Loaded at the top of every protected page. Validates the session and
 * defines the global helper functions used throughout the application.
 *
 * @package ProyectoBase
 * @subpackage Views\Layouts
 * @author Jandres25
 * @version 1.0
 */

if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Load configuration from .env
try {
    $env_file = __DIR__ . '/../../.env';

    if (!file_exists($env_file)) {
        die('Error: .env file not found. Please create a .env file with APP_URL and other required variables.');
    }

    $env_lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (strpos(trim($line), '#') === 0 || empty(trim($line))) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name  = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }

    $app_url = $_ENV['APP_URL'] ?? getenv('APP_URL');

    if (empty($app_url)) {
        die('Error: APP_URL is not set in .env. Please add APP_URL=your_domain/ to the .env file.');
    }

    if (substr($app_url, -1) !== '/') {
        $app_url .= '/';
    }

    $GLOBALS['URL'] = $app_url;
    $URL = $GLOBALS['URL'];

    $GLOBALS['APP_VERSION'] = $_ENV['APP_VERSION'] ?? getenv('APP_VERSION') ?: '1.0.0';
    $APP_VERSION = $GLOBALS['APP_VERSION'];
} catch (Exception $e) {
    die('Error loading configuration: ' . $e->getMessage() . '. Please check that .env is configured correctly.');
}

/**
 * Returns true if the current request has an authenticated session.
 *
 * @return bool
 */
function isAuthenticated()
{
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

/**
 * Checks for session inactivity and destroys the session if it has expired.
 *
 * @param int $timeout  Inactivity timeout in seconds (default: 86400 = 1 day)
 * @return bool  True if the session is still active, false if it expired
 */
function checkSessionTimeout($timeout = 86400)
{
    if (isset($_SESSION['last_access'])) {
        $inactive = time() - $_SESSION['last_access'];

        if ($inactive >= $timeout) {
            session_unset();
            session_destroy();
            return false;
        }
    }

    $_SESSION['last_access'] = time();
    return true;
}

/**
 * Detects possible session hijacking by comparing IP and user-agent.
 *
 * @return bool  True if the session is secure, false if hijacking is suspected
 */
function checkSessionSecurity()
{
    if (isset($_SESSION['ip']) && isset($_SESSION['user_agent'])) {
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

/**
 * Redirects to the login page if the user is not authenticated.
 *
 * @param string|null $redirectUrl  Override the default login URL
 */
function requireLogin($redirectUrl = null)
{
    global $URL;

    if (!$redirectUrl) {
        $redirectUrl = $URL . 'views/login/login.php';
    }

    if (!isAuthenticated() || !checkSessionTimeout() || !checkSessionSecurity()) {
        if (isset($_SESSION)) {
            if (!isAuthenticated()) {
                $_SESSION['message'] = 'You must log in to access this page.';
            } else {
                $_SESSION['message'] = 'Session expired due to inactivity. Please log in again.';
            }
            $_SESSION['icon'] = 'warning';
        }

        header('Location: ' . $redirectUrl);
        exit;
    }

    refreshPermissionsIfStale();
}

/**
 * Redirects to the dashboard if the user does not have one of the required roles.
 *
 * @param array       $allowedRoles
 * @param string|null $redirectUrl
 */
function requireRole($allowedRoles, $redirectUrl = null)
{
    global $URL;

    if (!$redirectUrl) {
        $redirectUrl = $URL . 'index.php';
    }

    requireLogin();

    $userPosition = $_SESSION['user_position'] ?? '';

    if (!in_array($userPosition, $allowedRoles)) {
        $_SESSION['message'] = 'You do not have permission to access this section.';
        $_SESSION['icon']    = 'error';
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Returns the current authenticated user's data, or null if not logged in.
 *
 * @return array|null  Keys: id, name, email, position, image
 */
function getCurrentUser()
{
    if (isAuthenticated()) {
        return [
            'id'       => $_SESSION['user_id']       ?? null,
            'name'     => $_SESSION['user_name']      ?? null,
            'email'    => $_SESSION['user_email']     ?? null,
            'position' => $_SESSION['user_position']  ?? null,
            'image'    => $_SESSION['user_image']     ?? 'public/img/user_default.jpg',
        ];
    }
    return null;
}

/**
 * Generates (or returns the existing) CSRF token for the current session.
 *
 * @return string
 */
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Returns true if the given token matches the session CSRF token.
 *
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token)
{
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Replaces the session CSRF token with a fresh one (call after every successful POST).
 *
 * @return string  The new token
 */
function regenerateCSRFToken()
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

/**
 * Refreshes the session permission cache if an admin has changed the user's permissions.
 * Compares the session timestamp against the value stored in the DB.
 * Called automatically by requireLogin() on every page load.
 */
function refreshPermissionsIfStale(): void
{
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId || !isset($_SESSION['user_permissions'])) {
        return;
    }

    $userModel   = new \Models\User();
    $dbTimestamp = $userModel->getPermissionsTimestamp($userId);

    $sessionTs = $_SESSION['permissions_ts'] ?? null;
    if ($dbTimestamp && (!$sessionTs || $dbTimestamp > $sessionTs)) {
        $authService = new \Services\AuthorizationService();
        $permissions = $authService->getUserPermissions($userId);
        $_SESSION['user_permissions'] = array_column($permissions, 'name');
        $_SESSION['permissions_ts']   = $dbTimestamp;
    }
}

/**
 * Renders the 403 error page and exits if the user lacks the required permission.
 *
 * @param string $permission  Permission name required to access the page
 */
function requirePermission(string $permission): void
{
    $userId      = $_SESSION['user_id'] ?? null;
    $authService = new \Services\AuthorizationService();

    if (!$authService->hasPermissionByName($userId, $permission)) {
        require __DIR__ . '/../errors/403.php';
        exit;
    }
}
