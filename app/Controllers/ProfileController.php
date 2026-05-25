<?php

/**
 * Profile Controller
 *
 * Manages the authenticated user's own profile.
 *
 * @package ProyectoBase
 * @subpackage App\Controllers
 * @author Jandres25
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ImageService;

class ProfileController extends Controller
{
    protected $model;
    private ImageService $imageService;

    public function __construct()
    {
        $this->model        = new User();
        $this->imageService = new ImageService(__DIR__ . '/../../../public/uploads/users/');
    }

    /**
     * Processes the profile-update form (phone, address, image).
     *
     * @return array  Result with keys: success, message, icon, redirect
     */
    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return ['success' => false, 'message' => 'Access not allowed.', 'icon' => 'warning', 'redirect' => 'views/users/profile.php'];
        }

        if (!Auth::check()) {
            return ['success' => false, 'message' => 'Session not started.', 'icon' => 'error', 'redirect' => 'views/auth/login.php'];
        }

        $id          = Auth::id();
        $currentUser = $this->model->getById($id);

        if (!$currentUser) {
            return ['success' => false, 'message' => 'User not found.', 'icon' => 'error', 'redirect' => 'views/users/profile.php'];
        }

        $oldImage = $currentUser['image'];

        $data = [
            'phone'   => !empty($_POST['phone'])   ? trim($_POST['phone'])   : null,
            'address' => !empty($_POST['address']) ? trim($_POST['address']) : null,
            'image'   => $oldImage,
        ];

        $errors = $this->model->validateProfileData($data);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(' ', $errors), 'icon' => 'warning', 'redirect' => 'views/users/profile.php'];
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $newImagePath = $this->imageService->processImage($_FILES['image']);
            if ($newImagePath) {
                $data['image'] = $newImagePath;
                if ($oldImage && $oldImage !== 'user_default.jpg') {
                    $this->imageService->deleteImage($oldImage);
                }
            } else {
                return ['success' => false, 'message' => 'Error processing image. Check format (JPG, PNG, WEBP) and that it does not exceed 5 MB.', 'icon' => 'error', 'redirect' => 'views/users/profile.php'];
            }
        }

        if ($this->model->updateProfile($id, $data)) {
            if ($data['image'] !== $oldImage) {
                $_SESSION['user_image'] = $data['image'];
            }
            $avatarChanged = $data['image'] !== $oldImage;
            AuditLogger::log(
                'users',
                $avatarChanged ? 'avatar_changed' : 'profile_updated',
                $avatarChanged ? 'User changed their avatar' : 'User updated their profile',
                ['user_id' => $id, 'avatar_changed' => $avatarChanged]
            );
            return ['success' => true, 'message' => 'Profile updated successfully.', 'icon' => 'success', 'redirect' => 'views/users/profile.php'];
        }

        return ['success' => false, 'message' => 'Error updating profile: ' . ($this->model->getLastError() ?: 'Unknown error.'), 'icon' => 'error', 'redirect' => 'views/users/profile.php'];
    }

    public function updatePasswordAjax()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return ['success' => false, 'message' => 'Access not allowed.'];
        }

        if (!Auth::check()) {
            return ['success' => false, 'message' => 'Session not started.'];
        }

        $id              = Auth::id();
        $currentPassword = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
        $newPassword     = isset($_POST['new_password'])     ? trim($_POST['new_password'])     : '';
        $confirmPassword = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return ['success' => false, 'message' => 'All password fields are required.'];
        }

        if (!$this->model->verifyCurrentPassword($id, $currentPassword)) {
            return ['success' => false, 'message' => 'The current password is incorrect.'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'New passwords do not match.'];
        }

        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
        }

        if ($this->model->updatePassword($id, $newPassword)) {
            return ['success' => true, 'message' => 'Password updated successfully.'];
        }

        return ['success' => false, 'message' => 'Error updating password: ' . $this->model->getLastError()];
    }
}
