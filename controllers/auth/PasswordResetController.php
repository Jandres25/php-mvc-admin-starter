<?php

namespace Controllers\Auth;

use Models\User;
use Services\MailService;

class PasswordResetController
{
    private $userModel;
    private $mailService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->mailService = new MailService();

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Handles the request for a password reset token.
     */
    public function requestReset()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['message'] = 'Security error.';
                $_SESSION['icon']    = 'error';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            $email = isset($_POST['email']) ? trim($_POST['email']) : '';

            if (empty($email)) {
                $_SESSION['message'] = 'Email is required.';
                $_SESSION['icon']    = 'error';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            if (!$this->userModel->emailExists($email)) {
                if (env('DEBUG')) {
                    $_SESSION['message'] = 'The email address is not registered in our system (DEBUG mode).';
                    $_SESSION['icon']    = 'error';
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                } else {
                    // For security, don't confirm the user doesn't exist in production.
                    $_SESSION['message'] = 'If the email is registered, you will receive a reset link.';
                    $_SESSION['icon']    = 'success';
                    header('Location: ' . $GLOBALS['URL'] . 'views/auth/login.php');
                }
                exit;
            }

            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            if ($this->userModel->setResetToken($email, $token, $expiry)) {
                $this->mailService->sendPasswordResetEmail($email, $token);
            }

            $_SESSION['message'] = 'If the email is registered, you will receive a reset link.';
            $_SESSION['icon']    = 'success';
            header('Location: ' . $GLOBALS['URL'] . 'views/auth/login.php');
            exit;
        }
    }

    /**
     * Handles the password reset process.
     */
    public function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['message'] = 'Security error.';
                $_SESSION['icon']    = 'error';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            $token    = $_POST['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';

            if (empty($token) || empty($password) || $password !== $confirm) {
                $_SESSION['message'] = 'Invalid data or passwords do not match.';
                $_SESSION['icon']    = 'error';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            if (strlen($password) < 8) {
                $_SESSION['message'] = 'Password must be at least 8 characters.';
                $_SESSION['icon']    = 'error';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            $user = $this->userModel->getUserByResetToken($token);

            if (!$user) {
                $_SESSION['message'] = 'The link has expired or is invalid.';
                $_SESSION['icon']    = 'error';
                header('Location: ' . $GLOBALS['URL'] . 'views/auth/login.php');
                exit;
            }

            if ($this->userModel->resetPassword($user['id'], $password)) {
                $_SESSION['message'] = 'Password updated successfully. You can now log in.';
                $_SESSION['icon']    = 'success';
                header('Location: ' . $GLOBALS['URL'] . 'views/auth/login.php');
            } else {
                $_SESSION['message'] = 'Error updating password.';
                $_SESSION['icon']    = 'error';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            }
            exit;
        }
    }
}
