<?php

/**
 * Authentication Controller
 *
 * Handles user login and logout.
 *
 * @package ProyectoBase
 * @subpackage Controllers\Auth
 * @author Jandres25
 * @version 1.0
 */

namespace Controllers\Auth;

use Models\User;

class AuthController
{
    /**
     * User model
     * @var User
     */
    private $model;

    public function __construct()
    {
        $this->model = new User();

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Renders the login form.
     */
    public function showLoginForm()
    {
        require_once __DIR__ . '/../../views/auth/login.php';
    }

    /**
     * Processes the login form (POST).
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['message'] = 'Security error. Please try again.';
                $_SESSION['icon']    = 'error';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            $identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
            $password   = isset($_POST['password'])   ? trim($_POST['password'])   : '';
            $errors     = [];

            if (empty($identifier)) {
                $errors[] = 'Please enter your email or document number.';
            }

            if (empty($password)) {
                $errors[] = 'Please enter your password.';
            }

            if (!empty($errors)) {
                $_SESSION['message'] = $errors[0];
                $_SESSION['icon']    = 'error';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

            if ($isEmail) {
                $user = $this->model->loginByEmail($identifier, $password);
            } else {
                $user = $this->model->loginByDocumentNumber($identifier, $password);
            }

            if (!$user) {
                $_SESSION['message'] = 'Incorrect credentials.';
                $_SESSION['icon']    = 'error';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            if ((int)$user['status'] === 0) {
                $_SESSION['message'] = 'Your account is deactivated. Please contact an administrator.';
                $_SESSION['icon']    = 'warning';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            $this->initSession($user);

            $_SESSION['message'] = 'Welcome, ' . $_SESSION['user_name'];
            $_SESSION['icon']    = 'success';
            header('Location: ' . $GLOBALS['URL']);
            exit;
        }

        header('Location: ' . $GLOBALS['URL'] . 'views/auth/login.php');
    }

    /**
     * Stores the authenticated user's data in the session.
     *
     * @param array $user  User row from the database
     */
    private function initSession($user)
    {
        session_regenerate_id(true);

        $_SESSION['user_id']       = $user['id'];
        $_SESSION['user_name']     = $user['name'] . ' ' . $user['first_surname'];
        $_SESSION['user_email']    = $user['email'];
        $_SESSION['user_position'] = $user['position'];
        $_SESSION['user_image']    = $user['image'] ?? 'user_default.jpg';
        $_SESSION['authenticated'] = true;
        $_SESSION['last_access']   = time();
        $_SESSION['ip']            = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent']    = $_SERVER['HTTP_USER_AGENT'];

        if (strtolower($user['position']) === 'administrator') {
            $_SESSION['user_permissions'] = ['*'];
        } else {
            $authService = new \Services\AuthorizationService();
            $permissions = $authService->getUserPermissions($user['id']);
            $_SESSION['user_permissions'] = array_column($permissions, 'name');
        }
    }

    /**
     * Destroys the session and redirects to the login page.
     */
    public function logout()
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: ' . $GLOBALS['URL'] . 'views/auth/login.php');
        exit;
    }
}
