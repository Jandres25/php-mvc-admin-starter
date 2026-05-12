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
        if (!\App\Core\Auth::hasPermission($this->permission)) {
            \App\Core\ErrorHandler::forbidden();
        }
    }
}
