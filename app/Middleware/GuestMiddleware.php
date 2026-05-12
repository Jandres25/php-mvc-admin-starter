<?php

namespace App\Middleware;

use App\Core\Auth;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (!Auth::check()) {
            Auth::attemptRememberLogin();
        }
        if (Auth::check()) {
            header('Location: ' . URL);
            exit;
        }
    }
}
