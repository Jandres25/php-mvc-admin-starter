<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\PermissionMiddleware;
use InvalidArgumentException;
use ReflectionProperty;
use Tests\TestCase;

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        parent::setUp();

        // Instantiate Router and override $routes via reflection to avoid
        // loading routes/web.php (which requires controllers to exist).
        $this->router = new Router();
        $this->setRoutes([]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function setRoutes(array $routes): void
    {
        $prop = new ReflectionProperty(Router::class, 'routes');
        $prop->setAccessible(true);
        $prop->setValue($this->router, $routes);
    }

    private function matchRoute(string $uri, array $route, string $method): bool
    {
        return $this->invokePrivate($this->router, 'matchRoute', [$uri, $route, $method]);
    }

    private function resolveMiddleware(string $name): object
    {
        return $this->invokePrivate($this->router, 'resolveMiddleware', [$name]);
    }

    // -------------------------------------------------------------------------
    // matchRoute — static paths
    // -------------------------------------------------------------------------

    public function test_matches_static_route_exactly(): void
    {
        $route = ['method' => 'GET', 'path' => '/dashboard', 'controller' => 'Dashboard\Dashboard@index'];
        $this->assertTrue($this->matchRoute('/dashboard', $route, 'GET'));
    }

    public function test_does_not_match_partial_static_path(): void
    {
        $route = ['method' => 'GET', 'path' => '/users', 'controller' => 'Users\User@index'];
        $this->assertFalse($this->matchRoute('/users/create', $route, 'GET'));
    }

    public function test_does_not_match_wrong_http_method(): void
    {
        $route = ['method' => 'GET', 'path' => '/users', 'controller' => 'Users\User@index'];
        $this->assertFalse($this->matchRoute('/users', $route, 'POST'));
    }

    public function test_method_matching_is_case_insensitive(): void
    {
        $route = ['method' => 'post', 'path' => '/users', 'controller' => 'Users\User@store'];
        $this->assertTrue($this->matchRoute('/users', $route, 'POST'));
    }

    public function test_matches_root_path(): void
    {
        $route = ['method' => 'GET', 'path' => '/', 'controller' => 'Dashboard\Dashboard@index'];
        $this->assertTrue($this->matchRoute('/', $route, 'GET'));
    }

    // -------------------------------------------------------------------------
    // matchRoute — dynamic params
    // -------------------------------------------------------------------------

    public function test_matches_route_with_numeric_param_and_captures_it(): void
    {
        $route = ['method' => 'GET', 'path' => '/users/(\d+)', 'controller' => 'Users\User@show'];
        $this->assertTrue($this->matchRoute('/users/5', $route, 'GET'));

        // Params should be stored after match
        $prop = new ReflectionProperty(Router::class, 'params');
        $prop->setAccessible(true);
        $params = $prop->getValue($this->router);
        $this->assertSame(['5'], $params);
    }

    public function test_matches_nested_route_with_numeric_param(): void
    {
        $route = ['method' => 'GET', 'path' => '/users/(\d+)/edit', 'controller' => 'Users\User@edit'];
        $this->assertTrue($this->matchRoute('/users/42/edit', $route, 'GET'));
    }

    public function test_does_not_match_non_numeric_param(): void
    {
        $route = ['method' => 'GET', 'path' => '/users/(\d+)', 'controller' => 'Users\User@show'];
        $this->assertFalse($this->matchRoute('/users/abc', $route, 'GET'));
    }

    public function test_captures_multiple_numeric_params(): void
    {
        $route = ['method' => 'GET', 'path' => '/posts/(\d+)/comments/(\d+)', 'controller' => 'Posts\Post@comment'];
        $this->assertTrue($this->matchRoute('/posts/3/comments/7', $route, 'GET'));

        $prop = new ReflectionProperty(Router::class, 'params');
        $prop->setAccessible(true);
        $params = $prop->getValue($this->router);
        $this->assertSame(['3', '7'], $params);
    }

    // -------------------------------------------------------------------------
    // resolveMiddleware
    // -------------------------------------------------------------------------

    public function test_resolves_auth_alias_to_auth_middleware(): void
    {
        $middleware = $this->resolveMiddleware('auth');
        $this->assertInstanceOf(AuthMiddleware::class, $middleware);
    }

    public function test_resolves_guest_alias_to_guest_middleware(): void
    {
        $middleware = $this->resolveMiddleware('guest');
        $this->assertInstanceOf(GuestMiddleware::class, $middleware);
    }

    public function test_resolves_perm_alias_to_permission_middleware(): void
    {
        $middleware = $this->resolveMiddleware('perm:users');
        $this->assertInstanceOf(PermissionMiddleware::class, $middleware);
    }

    public function test_throws_on_unknown_middleware_alias(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->resolveMiddleware('unknown');
    }
}
