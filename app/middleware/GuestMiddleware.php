<?php

namespace App\Middleware;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
            header('Location: ' . URL);
            exit;
        }
    }
}
