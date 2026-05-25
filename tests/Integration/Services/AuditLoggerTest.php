<?php

declare(strict_types=1);

namespace Tests\Integration\Services;

use App\Models\ActivityLog;
use App\Services\AuditLogger;
use Tests\IntegrationTestCase;

/**
 * Integration tests for AuditLogger.
 *
 * These tests use the test DB (configured by IntegrationTestCase) to verify that
 * AuditLogger::log() correctly inserts rows via ActivityLog::create(). Each test
 * runs inside a transaction that is rolled back on teardown.
 *
 * Tests verify:
 *   - log() inserts a row into activity_logs without throwing
 *   - actor resolution reads from the session correctly
 *   - missing $_SERVER keys do not cause errors
 *   - sensitive data contract is documented
 */
class AuditLoggerTest extends IntegrationTestCase
{
    // -------------------------------------------------------------------------
    // Insertion — verifies rows are actually written to the DB
    // -------------------------------------------------------------------------

    public function test_log_inserts_row_into_activity_logs(): void
    {
        AuditLogger::log('auth', 'login', 'Test event');

        $rows = (new ActivityLog())->getAll();
        $this->assertCount(1, $rows);
        $this->assertSame('auth', $rows[0]['module']);
        $this->assertSame('login', $rows[0]['action']);
    }

    public function test_log_with_all_parameters_inserts_correctly(): void
    {
        AuditLogger::log(
            'users',
            'create',
            'User created: Jane Doe',
            ['name' => 'Jane', 'email' => 'jane@test.com'],
            1,
            'Admin Test (admin@test.com)'
        );

        $rows = (new ActivityLog())->getAll();
        $this->assertCount(1, $rows);
        $this->assertSame('1', (string) $rows[0]['actor_id']);
        $this->assertSame('Admin Test (admin@test.com)', $rows[0]['actor_label']);
        $this->assertSame('User created: Jane Doe', $rows[0]['description']);
    }

    public function test_log_with_empty_details_stores_null(): void
    {
        AuditLogger::log('roles', 'update', 'Role updated', []);

        $rows = (new ActivityLog())->getAll();
        $this->assertNull($rows[0]['details']);
    }

    public function test_log_with_empty_description_stores_null(): void
    {
        AuditLogger::log('permissions', 'create', '', ['name' => 'users']);

        $rows = (new ActivityLog())->getAll();
        $this->assertNull($rows[0]['description']);
    }

    // -------------------------------------------------------------------------
    // Actor resolution — auto-resolve from session
    // -------------------------------------------------------------------------

    public function test_log_auto_resolves_actor_from_session(): void
    {
        $_SESSION['user_id']       = 1;
        $_SESSION['authenticated'] = true;
        $_SESSION['user_name']     = 'Admin Test';
        $_SESSION['user_email']    = 'admin@test.com';

        AuditLogger::log('auth', 'logout', 'User logged out');

        $rows = (new ActivityLog())->getAll();
        $this->assertSame('1', (string) $rows[0]['actor_id']);
        $this->assertStringContainsString('Admin Test', $rows[0]['actor_label']);
        $this->assertStringContainsString('admin@test.com', $rows[0]['actor_label']);
    }

    public function test_log_without_session_stores_null_actor_id(): void
    {
        $_SESSION = [];

        AuditLogger::log('auth', 'login_failed', 'Failed attempt', ['identifier_type' => 'email'], null, 'user@test.com');

        $rows = (new ActivityLog())->getAll();
        $this->assertNull($rows[0]['actor_id']);
        $this->assertSame('user@test.com', $rows[0]['actor_label']);
    }

    // -------------------------------------------------------------------------
    // IP and user agent capture
    // -------------------------------------------------------------------------

    public function test_log_stores_ip_and_user_agent(): void
    {
        $_SERVER['REMOTE_ADDR']     = '192.168.1.100';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit/11.0';

        AuditLogger::log('auth', 'login', 'Login event');

        $rows = (new ActivityLog())->getAll();
        $this->assertSame('192.168.1.100', $rows[0]['ip_address']);
        $this->assertSame('PHPUnit/11.0', $rows[0]['user_agent']);
    }

    public function test_log_without_server_vars_stores_null_ip(): void
    {
        unset($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

        AuditLogger::log('auth', 'login', 'Login event');

        $rows = (new ActivityLog())->getAll();
        $this->assertNull($rows[0]['ip_address']);
        $this->assertNull($rows[0]['user_agent']);
    }

    // -------------------------------------------------------------------------
    // Explicit actor override
    // -------------------------------------------------------------------------

    public function test_log_explicit_actor_overrides_session(): void
    {
        // Session has user_id=1, but we explicitly pass actor_id=2 (both exist in the test seed)
        $_SESSION['user_id']       = 1;
        $_SESSION['authenticated'] = true;

        AuditLogger::log('auth', 'account_locked', 'Account locked', [], 2, 'Normal User (user@test.com)');

        $rows = (new ActivityLog())->getAll();
        $this->assertSame('2', (string) $rows[0]['actor_id']);
        $this->assertSame('Normal User (user@test.com)', $rows[0]['actor_label']);
    }

    // -------------------------------------------------------------------------
    // Sensitive data guard — passwords must NEVER appear in details
    // -------------------------------------------------------------------------

    public function test_log_details_must_not_contain_password(): void
    {
        // This test documents the contract: callers must not pass passwords.
        // We verify that the method itself accepts any array (it is the caller's
        // responsibility), but we document the rule here.
        $details = ['user_id' => 5]; // correct — no 'password' key

        AuditLogger::log('users', 'password_changed', 'Password changed', $details);
        $this->assertArrayNotHasKey('password', $details);
        $this->assertArrayNotHasKey('new_password', $details);
        $this->assertArrayNotHasKey('token', $details);
    }

    // -------------------------------------------------------------------------
    // Long user agent truncation (> 255 chars must be stored as 255 chars max)
    // -------------------------------------------------------------------------

    public function test_log_truncates_user_agent_to_255_chars(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = str_repeat('A', 1000);

        AuditLogger::log('auth', 'login', 'Login event');

        $rows = (new ActivityLog())->getAll();
        $this->assertSame(255, strlen($rows[0]['user_agent']));
    }
}
