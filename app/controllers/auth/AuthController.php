<?php

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLoginForm(): void
    {
        $this->renderStandalone('auth/login');
    }

    public function login(): void
    {
        $this->csrfCheck();

        $identifier = trim($_POST['identifier'] ?? '');
        $password   = trim($_POST['password']   ?? '');

        if (empty($identifier) || empty($password)) {
            $_SESSION['message'] = 'Please fill in all fields.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'login');
        }

        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
        $user    = $isEmail
            ? $this->userModel->loginByEmail($identifier, $password)
            : $this->userModel->loginByDocumentNumber($identifier, $password);

        if (!$user) {
            $_SESSION['message'] = 'Incorrect credentials.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'login');
        }

        if ((int) $user['status'] === 0) {
            $_SESSION['message'] = 'Your account is deactivated. Please contact an administrator.';
            $_SESSION['icon']    = 'warning';
            $this->redirect(URL . 'login');
        }

        $this->initSession($user);
        regenerateCSRFToken();

        $_SESSION['message'] = 'Welcome, ' . $_SESSION['user_name'];
        $_SESSION['icon']    = 'success';
        $this->redirect(URL);
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
        $this->redirect(URL . 'login');
    }

    public function showForgotPasswordForm(): void
    {
        $this->renderStandalone('auth/forgot_password');
    }

    public function showResetPasswordForm(): void
    {
        $token = trim($_GET['token'] ?? '');

        if (empty($token)) {
            $_SESSION['message'] = 'Invalid or missing token.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'login');
        }

        $user = $this->userModel->getUserByResetToken($token);

        if (!$user) {
            $_SESSION['message'] = 'The link has expired or is invalid.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'login');
        }

        $this->renderStandalone('auth/reset_password', compact('token'));
    }

    public function requestPasswordReset(): void
    {
        (new PasswordResetController())->requestReset();
    }

    public function resetPassword(): void
    {
        (new PasswordResetController())->resetPassword();
    }

    private function initSession(array $user): void
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
            $authService = new \App\Services\AuthorizationService();
            $permissions = $authService->getUserPermissions($user['id']);
            $_SESSION['user_permissions'] = array_column($permissions, 'name');
        }

        $_SESSION['permissions_ts'] = date('Y-m-d H:i:s');
    }
}
