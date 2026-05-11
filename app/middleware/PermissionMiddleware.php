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
            \App\Core\ErrorHandler::forbidden();
        }
    }
}
