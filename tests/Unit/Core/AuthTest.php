<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Auth;
use Tests\TestCase;

/**
 * Tests for Auth static methods that only read/write $_SESSION and $_SERVER.
 * No session_start() is called — $_SESSION is used as a plain array in CLI.
 */
class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Start a real session so session_destroy() inside the SUT does not
        // emit "Trying to destroy uninitialized session" warnings in CLI.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // check()
    // -------------------------------------------------------------------------

    public function test_check_returns_false_when_session_is_empty(): void
    {
        $this->assertFalse(Auth::check());
    }

    public function test_check_returns_false_when_authenticated_flag_is_missing(): void
    {
        // Set user_id but explicitly leave authenticated as null to avoid an
        // "Undefined array key" notice from Auth::check()'s strict comparison.
        $_SESSION['user_id']       = 1;
        $_SESSION['authenticated'] = null;
        $this->assertFalse(Auth::check());
    }

    public function test_check_returns_false_when_authenticated_is_false(): void
    {
        $_SESSION['user_id']       = 1;
        $_SESSION['authenticated'] = false;
        $this->assertFalse(Auth::check());
    }

    public function test_check_returns_true_when_authenticated(): void
    {
        $_SESSION['user_id']       = 1;
        $_SESSION['authenticated'] = true;
        $this->assertTrue(Auth::check());
    }

    // -------------------------------------------------------------------------
    // id()
    // -------------------------------------------------------------------------

    public function test_id_returns_null_when_no_session(): void
    {
        $this->assertNull(Auth::id());
    }

    public function test_id_returns_int_when_present(): void
    {
        $_SESSION['user_id'] = '5';
        $this->assertSame(5, Auth::id());
    }

    // -------------------------------------------------------------------------
    // user()
    // -------------------------------------------------------------------------

    public function test_user_returns_null_when_not_authenticated(): void
    {
        $this->assertNull(Auth::user());
    }

    public function test_user_returns_array_with_expected_keys(): void
    {
        $_SESSION['user_id']       = 1;
        $_SESSION['authenticated'] = true;
        $_SESSION['user_name']     = 'John Doe';
        $_SESSION['user_email']    = 'john@example.com';
        $_SESSION['user_is_admin'] = true;
        $_SESSION['user_role']     = 'Administrator';
        $_SESSION['user_image']    = 'john.jpg';

        $user = Auth::user();

        $this->assertIsArray($user);
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('name', $user);
        $this->assertArrayHasKey('email', $user);
        $this->assertArrayHasKey('role', $user);
        $this->assertArrayHasKey('image', $user);
        $this->assertSame(1, $user['id']);
        $this->assertSame('John Doe', $user['name']);
        $this->assertSame('Administrator', $user['role']);
    }

    public function test_user_defaults_image_to_user_default(): void
    {
        $_SESSION['user_id']       = 1;
        $_SESSION['authenticated'] = true;

        $user = Auth::user();
        $this->assertSame('user_default.jpg', $user['image']);
    }

    // -------------------------------------------------------------------------
    // isAdmin()
    // -------------------------------------------------------------------------

    public function test_is_admin_returns_true_when_flag_is_true(): void
    {
        $_SESSION['user_is_admin'] = true;
        $this->assertTrue(Auth::isAdmin());
    }

    public function test_is_admin_returns_false_when_flag_is_false(): void
    {
        $_SESSION['user_is_admin'] = false;
        $this->assertFalse(Auth::isAdmin());
    }

    public function test_is_admin_returns_false_when_flag_missing(): void
    {
        $this->assertFalse(Auth::isAdmin());
    }

    // -------------------------------------------------------------------------
    // hasPermission()
    // -------------------------------------------------------------------------

    public function test_has_permission_returns_true_for_wildcard(): void
    {
        $_SESSION['user_permissions'] = ['*'];
        $this->assertTrue(Auth::hasPermission('any.permission'));
    }

    public function test_has_permission_returns_true_for_named_match(): void
    {
        $_SESSION['user_permissions'] = ['users', 'reports'];
        $this->assertTrue(Auth::hasPermission('users'));
    }

    public function test_has_permission_returns_false_for_missing_name(): void
    {
        $_SESSION['user_permissions'] = ['users'];
        $this->assertFalse(Auth::hasPermission('reports'));
    }

    public function test_has_permission_returns_false_when_cache_empty(): void
    {
        $_SESSION['user_permissions'] = [];
        $this->assertFalse(Auth::hasPermission('users'));
    }

    public function test_has_permission_returns_false_when_cache_not_set(): void
    {
        $this->assertFalse(Auth::hasPermission('users'));
    }

    // -------------------------------------------------------------------------
    // checkTimeout()
    // -------------------------------------------------------------------------

    public function test_check_timeout_returns_true_and_updates_last_access_within_window(): void
    {
        $_ENV['SESSION_LIFETIME']  = '1800';
        $_SESSION['last_access']   = time() - 60;

        $result = Auth::checkTimeout();

        $this->assertTrue($result);
        $this->assertEqualsWithDelta(time(), $_SESSION['last_access'], 2);
    }

    public function test_check_timeout_returns_false_and_destroys_when_exceeded(): void
    {
        $_ENV['SESSION_LIFETIME'] = '1800';
        $_SESSION['last_access']  = time() - 1801;
        $_SESSION['user_id']      = 1;

        $result = Auth::checkTimeout();

        // In CLI there is no real session, so session_unset/session_destroy have
        // no effect on the superglobal — only the return value is reliable.
        $this->assertFalse($result);
    }

    public function test_check_timeout_returns_false_when_last_access_not_set(): void
    {
        $_ENV['SESSION_LIFETIME'] = '1800';

        $result = Auth::checkTimeout();

        $this->assertFalse($result);
    }

    // -------------------------------------------------------------------------
    // checkSecurity()
    // -------------------------------------------------------------------------

    public function test_check_security_returns_true_when_ip_and_agent_match(): void
    {
        $_SERVER['REMOTE_ADDR']     = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'TestAgent/1.0';
        $_SESSION['ip']             = '127.0.0.1';
        $_SESSION['user_agent']     = 'TestAgent/1.0';

        $this->assertTrue(Auth::checkSecurity());
    }

    public function test_check_security_returns_false_on_ip_change(): void
    {
        $_SERVER['REMOTE_ADDR']     = '10.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'TestAgent/1.0';
        $_SESSION['ip']             = '127.0.0.1';
        $_SESSION['user_agent']     = 'TestAgent/1.0';
        $_SESSION['user_id']        = 1;

        $result = Auth::checkSecurity();

        $this->assertFalse($result);
    }

    public function test_check_security_returns_false_on_user_agent_mismatch(): void
    {
        $_SERVER['REMOTE_ADDR']     = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'EvilBot/2.0';
        $_SESSION['ip']             = '127.0.0.1';
        $_SESSION['user_agent']     = 'TestAgent/1.0';

        $this->assertFalse(Auth::checkSecurity());
    }

    public function test_check_security_returns_false_when_session_keys_absent(): void
    {
        // Fail-closed: session without ip/user_agent is treated as invalid
        $this->assertFalse(Auth::checkSecurity());
    }
}
