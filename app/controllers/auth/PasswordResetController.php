<?php

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Models\User;
use App\Services\MailService;

class PasswordResetController extends Controller
{
    private User $userModel;
    private MailService $mailService;

    public function __construct()
    {
        $this->userModel   = new User();
        $this->mailService = new MailService();
    }

    public function requestReset(): void
    {
        $this->csrfCheck();

        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $_SESSION['message'] = 'Email is required.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'forgot-password');
        }

        // Generic message to avoid user enumeration
        $genericMessage = 'If the email is registered, you will receive a reset link.';

        if (!$this->userModel->emailExists($email)) {
            if (env('DEBUG')) {
                $_SESSION['message'] = 'The email address is not registered in our system (DEBUG mode).';
                $_SESSION['icon']    = 'error';
                $this->redirect(URL . 'forgot-password');
            }

            $_SESSION['message'] = $genericMessage;
            $_SESSION['icon']    = 'info';
            $this->redirect(URL . 'login');
        }

        $token  = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        if ($this->userModel->setResetToken($email, $token, $expiry)) {
            $this->mailService->sendPasswordResetEmail($email, $token);
        }

        regenerateCSRFToken();

        $_SESSION['message'] = $genericMessage;
        $_SESSION['icon']    = 'success';
        $this->redirect(URL . 'login');
    }

    public function resetPassword(): void
    {
        $this->csrfCheck();

        $token    = $_POST['token']            ?? '';
        $password = $_POST['password']         ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (empty($token) || empty($password) || $password !== $confirm) {
            $_SESSION['message'] = 'Invalid data or passwords do not match.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'reset-password?token=' . urlencode($token));
        }

        if (strlen($password) < 8) {
            $_SESSION['message'] = 'Password must be at least 8 characters.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'reset-password?token=' . urlencode($token));
        }

        $user = $this->userModel->getUserByResetToken($token);

        if (!$user) {
            $_SESSION['message'] = 'The link has expired or is invalid.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'login');
        }

        if ($this->userModel->resetPassword($user['id'], $password)) {
            regenerateCSRFToken();
            $_SESSION['message'] = 'Password updated successfully. You can now log in.';
            $_SESSION['icon']    = 'success';
        } else {
            $_SESSION['message'] = 'Error updating password. Please try again.';
            $_SESSION['icon']    = 'error';
        }

        $this->redirect(URL . 'login');
    }
}
