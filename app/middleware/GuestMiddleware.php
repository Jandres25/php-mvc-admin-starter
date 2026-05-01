<?php

namespace App\Middleware;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (!isAuthenticated()) {
            tryAutoLoginFromRememberCookie();
        }
        if (isAuthenticated()) {
            header('Location: ' . URL);
            exit;
        }
    }
}
