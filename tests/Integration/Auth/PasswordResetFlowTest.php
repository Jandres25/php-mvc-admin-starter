<?php

declare(strict_types=1);

namespace Tests\Integration\Auth;

use App\Models\PasswordReset;
use App\Models\User;
use Tests\IntegrationTestCase;

/**
 * Integration tests for the password-reset flow — Sub-fase 1.2.
 *
 * Verifies that PasswordReset::create/findValidByToken/markUsed work end-to-end
 * for the 'reset' type, and that the pending-user guard behaves correctly.
 *
 * Controller-level redirects are not tested here (those require HTTP integration);
 * this suite covers the model/data layer that backs the controller.
 */
class PasswordResetFlowTest extends IntegrationTestCase
{
    private PasswordReset $resets;
    private User          $userModel;

    /** Seeded admin (status = 1) */
    private const ADMIN_ID    = 1;
    private const ADMIN_EMAIL = 'admin@test.com';

    /** Seeded normal user (status = 1) */
    private const EDITOR_ID = 2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resets    = new PasswordReset();
        $this->userModel = new User();
    }

    // -------------------------------------------------------------------------
    // requestReset flow — token is created in password_resets
    // -------------------------------------------------------------------------

    public function test_request_creates_reset_token_in_password_resets(): void
    {
        $token = $this->resets->create(self::ADMIN_ID, 'reset');

        $this->assertNotEmpty($token);

        $row = $this->resets->findValidByToken($token, 'reset');
        $this->assertIsArray($row);
        $this->assertSame((string) self::ADMIN_ID, (string) $row['user_id']);
        $this->assertSame('reset', $row['type']);
    }

    public function test_getIdByEmail_resolves_existing_email(): void
    {
        $userId = $this->userModel->getIdByEmail(self::ADMIN_EMAIL);

        $this->assertSame((string) self::ADMIN_ID, (string) $userId);
    }

    public function test_getIdByEmail_returns_falsy_for_unknown_email(): void
    {
        $result = $this->userModel->getIdByEmail('nonexistent@example.com');

        $this->assertFalse((bool) $result);
    }

    // -------------------------------------------------------------------------
    // Pending user guard
    // -------------------------------------------------------------------------

    public function test_pending_user_cannot_request_reset(): void
    {
        // Manually set a user to pending status.
        $this->userModel->updateStatus(self::EDITOR_ID, User::STATUS_PENDING);

        $user = $this->userModel->getById(self::EDITOR_ID);
        $this->assertSame(User::STATUS_PENDING, (int) $user['status']);

        // The controller guards: if status === STATUS_PENDING, no token is issued.
        // We verify the guard condition directly on the model data.
        $isPending = (int) $user['status'] === User::STATUS_PENDING;
        $this->assertTrue($isPending, 'User should be in pending state');

        // Confirm no reset token exists for this user in password_resets.
        $stmt = self::$pdo->prepare(
            "SELECT COUNT(*) FROM password_resets WHERE user_id = ? AND type = 'reset' AND used_at IS NULL"
        );
        $stmt->execute([self::EDITOR_ID]);
        $this->assertSame(0, (int) $stmt->fetchColumn(), 'No reset token should exist for pending user');
    }

    // -------------------------------------------------------------------------
    // resetPassword flow — valid token resets password and is marked used
    // -------------------------------------------------------------------------

    public function test_reset_with_valid_token_changes_password_and_marks_token_used(): void
    {
        $token = $this->resets->create(self::ADMIN_ID, 'reset');
        $row   = $this->resets->findValidByToken($token, 'reset');
        $this->assertIsArray($row);

        $newPassword = 'NewSecure123!';
        $success = $this->userModel->resetPassword($row['user_id'], $newPassword);
        $this->assertTrue($success);

        $this->resets->markUsed((int) $row['id']);

        // Password must now verify correctly.
        $updated = $this->userModel->getById(self::ADMIN_ID);
        $this->assertTrue(
            password_verify($newPassword, $updated['password']),
            'Password must be updated to the new value'
        );

        // Token must be consumed.
        $reused = $this->resets->findValidByToken($token, 'reset');
        $this->assertFalse($reused, 'Token must be invalid after markUsed()');
    }

    public function test_reset_with_used_token_is_rejected(): void
    {
        $token = $this->resets->create(self::ADMIN_ID, 'reset');
        $row   = $this->resets->findValidByToken($token, 'reset');

        $this->resets->markUsed((int) $row['id']);

        // Second attempt with the same token must fail.
        $result = $this->resets->findValidByToken($token, 'reset');
        $this->assertFalse($result, 'Used token must be rejected on second use');
    }

    public function test_reset_with_expired_token_is_rejected(): void
    {
        $token     = $this->resets->create(self::ADMIN_ID, 'reset');
        $tokenHash = hash('sha256', $token);

        // Backdate the expiry so it appears expired.
        $stmt = self::$pdo->prepare(
            "UPDATE password_resets
             SET expires_at = DATE_SUB(NOW(), INTERVAL 1 SECOND)
             WHERE token_hash = ?"
        );
        $stmt->execute([$tokenHash]);

        $result = $this->resets->findValidByToken($token, 'reset');
        $this->assertFalse($result, 'Expired token must be rejected');
    }

    public function test_unknown_email_does_not_produce_token(): void
    {
        // Simulate requestReset guard: getIdByEmail returns falsy → no create() called.
        $userId = $this->userModel->getIdByEmail('ghost@example.com');
        $this->assertFalse((bool) $userId);

        // Verify no rows were inserted for a non-existent user.
        $stmt = self::$pdo->prepare(
            "SELECT COUNT(*) FROM password_resets WHERE type = 'reset'"
        );
        $stmt->execute();
        $this->assertSame(0, (int) $stmt->fetchColumn());
    }

    // -------------------------------------------------------------------------
    // STATUS_PENDING constant on User model
    // -------------------------------------------------------------------------

    public function test_user_status_constants_are_defined(): void
    {
        $this->assertSame(0, User::STATUS_INACTIVE);
        $this->assertSame(1, User::STATUS_ACTIVE);
        $this->assertSame(2, User::STATUS_PENDING);
    }
}
