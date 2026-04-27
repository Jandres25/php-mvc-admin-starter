<?php

namespace App\Middleware;

class PermissionMiddleware implements MiddlewareInterface
{
    private string $permission;

    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }

    public function handle(): void
    {
        $userId      = $_SESSION['user_id'] ?? null;
        $authService = new \App\Services\AuthorizationService();

        if (!$authService->hasPermissionByName($userId, $this->permission)) {
            http_response_code(403);
            require dirname(__DIR__, 2) . '/views/errors/403.php';
            exit;
        }
    }
}
