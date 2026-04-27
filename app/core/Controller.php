<?php

namespace App\Core;

abstract class Controller
{
    protected $model;

    public function render($view, $data = [], $plugins = [], $module_scripts = [], $module_styles = [])
    {
        extract($data);

        $currentUser = getCurrentUser();
        $authService = new \App\Services\AuthorizationService();

        $layoutData = [
            'content'        => $this->getView($view, $data),
            'plugins'        => $plugins,
            'module_scripts' => $module_scripts,
            'module_styles'  => $module_styles,
        ];

        require dirname(__DIR__, 2) . '/views/layouts/header.php';
        echo $layoutData['content'];
        require dirname(__DIR__, 2) . '/views/layouts/footer.php';
    }

    public function renderStandalone($view, $data = [])
    {
        $viewPath = dirname(__DIR__, 2) . '/views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: $viewPath");
        }

        extract($data);
        require $viewPath;
    }

    protected function getView($view, $data = [])
    {
        $viewPath = dirname(__DIR__, 2) . '/views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: $viewPath");
        }

        extract($data);
        ob_start();
        require $viewPath;
        return ob_get_clean();
    }

    public function redirect($path)
    {
        header('Location: ' . $path);
        exit;
    }

    public function jsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    public function csrfCheck()
    {
        $token = $_POST['csrf_token'] ?? '';

        if (!verifyCSRFToken($token)) {
            $isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');

            if ($isAjax) {
                $this->jsonResponse(['success' => false, 'message' => 'Token inválido'], 403);
            } else {
                $_SESSION['message'] = 'Token de seguridad inválido. Por favor, intenta de nuevo.';
                $_SESSION['icon']    = 'error';
                $this->redirect($_SERVER['HTTP_REFERER'] ?? URL);
            }
        }
    }

    public function param($key, $default = null)
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    protected function requireLogin()
    {
        if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
            $this->redirect(URL . 'login');
        }
    }

    protected function requirePermission($permission)
    {
        $this->requireLogin();

        $userId      = $_SESSION['user_id'] ?? null;
        $authService = new \App\Services\AuthorizationService();

        if (!$authService->hasPermissionByName($userId, $permission)) {
            http_response_code(403);
            $this->renderStandalone('errors/403');
            exit;
        }
    }
}
