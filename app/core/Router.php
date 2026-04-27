<?php

namespace App\Core;

class Router
{
    protected $routes = [];
    protected $params = [];

    public function __construct()
    {
        $this->routes = require dirname(__DIR__, 2) . '/routes/web.php';
    }

    public function dispatch($uri)
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $uri = parse_url($uri, PHP_URL_PATH);

        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '/';
        if ($basePath !== '/' && strpos((string) $uri, $basePath) === 0) {
            $uri = substr((string) $uri, strlen($basePath));
        }

        if (strpos((string) $uri, '/index.php') === 0) {
            $uri = substr((string) $uri, strlen('/index.php'));
        }

        $uri = '/' . ltrim((string) $uri, '/');
        if ($uri === '//') {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($this->matchRoute($uri, $route, $method)) {
                $this->runMiddleware($route['middleware'] ?? []);
                return $this->callController($route['controller']);
            }
        }

        http_response_code(404);
        $errorView = dirname(__DIR__, 2) . '/views/errors/404.php';
        if (file_exists($errorView)) {
            require $errorView;
        } else {
            echo '404 - Page Not Found';
        }
        exit;
    }

    protected function runMiddleware(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            $this->resolveMiddleware($middleware)->handle();
        }
    }

    protected function resolveMiddleware(string $name): \App\Middleware\MiddlewareInterface
    {
        if ($name === 'auth') {
            return new \App\Middleware\AuthMiddleware();
        }
        if ($name === 'guest') {
            return new \App\Middleware\GuestMiddleware();
        }
        if (strpos($name, 'perm:') === 0) {
            return new \App\Middleware\PermissionMiddleware(substr($name, 5));
        }
        throw new \InvalidArgumentException("Middleware desconocido: $name");
    }

    protected function matchRoute($uri, $route, $method)
    {
        if (strtoupper($route['method'] ?? 'GET') !== strtoupper($method)) {
            return false;
        }

        $pattern = '@^' . preg_replace('/\(\d+\)/', '(\d+)', (string) $route['path']) . '$@';
        $pattern = str_replace('/', '\/', $pattern);

        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);
            $this->params = $matches;
            return true;
        }

        return false;
    }

    protected function callController($controller)
    {
        [$controllerName, $methodName] = explode('@', $controller);

        $controllerClass = 'App\\Controllers\\' . $controllerName;
        if (substr($controllerClass, -10) !== 'Controller') {
            $controllerClass .= 'Controller';
        }

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            echo "Controller not found: $controllerClass";
            exit;
        }

        $instance = new $controllerClass();

        if (!method_exists($instance, $methodName)) {
            http_response_code(500);
            echo "Method not found: $methodName in $controllerClass";
            exit;
        }

        return call_user_func_array([$instance, $methodName], $this->params);
    }
}
