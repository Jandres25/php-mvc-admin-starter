<?php

namespace App\Controllers\Auth;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Permission;
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

        $permModel = new Permission();
        $permNames = strtolower($user['position'] ?? '') === 'administrator'
            ? ['*']
            : array_column($permModel->getByUserId((int) $user['id']), 'name');

        Auth::login($user, $permNames);
        regenerateCSRFToken();

        if (!empty($_POST['remember']) && $_POST['remember'] === '1') {
            Auth::issueRememberCookie((int) $user['id']);
        }

        $_SESSION['welcome_user'] = $_SESSION['user_name'];
        $this->redirect(URL);
    }

    public function logout(): void
    {
        Auth::logout((int) ($_SESSION['user_id'] ?? 0));
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

}
