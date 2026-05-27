<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Models\PasswordReset;
use Tests\IntegrationTestCase;

/**
 * Integration tests for PasswordReset model — Sub-fase 1.1.
 *
 * Each test runs inside a transaction that is rolled back on teardown,
 * so the password_resets table stays clean between tests.
 */
class PasswordResetTest extends IntegrationTestCase
{
    private PasswordReset $model;

    /** User IDs available from minimal_seed.sql */
    private const ADMIN_ID  = 1;
    private const EDITOR_ID = 2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new PasswordReset();
    }

    // -------------------------------------------------------------------------
    // create()
    // -------------------------------------------------------------------------

    public function test_create_persists_hashed_token_not_plain(): void
    {
        $rawToken = $this->model->create(self::ADMIN_ID, 'reset');

        $this->assertNotEmpty($rawToken);

        // The plain token must NOT be in the DB.
        $stmt = self::$pdo->prepare(
            "SELECT token_hash FROM password_resets WHERE user_id = ? LIMIT 1"
        );
        $stmt->execute([self::ADMIN_ID]);
        $storedHash = $stmt->fetchColumn();

        $this->assertNotFalse($storedHash, 'No row was inserted');
        $this->assertNotSame($rawToken, $storedHash, 'Plain token must not be stored');
        $this->assertSame(hash('sha256', $rawToken), $storedHash, 'Stored hash must be SHA-256 of raw token');
    }

    public function test_create_with_reset_type_expires_in_1h(): void
    {
        $this->model->create(self::ADMIN_ID, 'reset');

        $stmt = self::$pdo->prepare(
            "SELECT expires_at FROM password_resets WHERE user_id = ? AND type = 'reset' LIMIT 1"
        );
        $stmt->execute([self::ADMIN_ID]);
        $expiresAt = $stmt->fetchColumn();

        $this->assertNotFalse($expiresAt);

        $diff = strtotime($expiresAt) - time();
        // Should be ~3600 s; allow ±30 s margin for test execution time.
        $this->assertGreaterThan(3570, $diff, 'Reset token should expire in ~1 hour');
        $this->assertLessThanOrEqual(3600, $diff, 'Reset token expiry must not exceed 1 hour');
    }

    public function test_create_with_invitation_type_expires_in_48h(): void
    {
        $this->model->create(self::ADMIN_ID, 'invitation');

        $stmt = self::$pdo->prepare(
            "SELECT expires_at FROM password_resets WHERE user_id = ? AND type = 'invitation' LIMIT 1"
        );
        $stmt->execute([self::ADMIN_ID]);
        $expiresAt = $stmt->fetchColumn();

        $this->assertNotFalse($expiresAt);

        $diff = strtotime($expiresAt) - time();
        $expectedSeconds = 48 * 3600;
        $this->assertGreaterThan($expectedSeconds - 30, $diff, 'Invitation token should expire in ~48 hours');
        $this->assertLessThanOrEqual($expectedSeconds, $diff, 'Invitation token expiry must not exceed 48 hours');
    }

    public function test_create_invalidates_previous_live_tokens_of_same_type(): void
    {
        // Issue two reset tokens for the same user.
        $firstToken  = $this->model->create(self::ADMIN_ID, 'reset');
        $secondToken = $this->model->create(self::ADMIN_ID, 'reset');

        // The first token must now be marked as used.
        $firstResult = $this->model->findValidByToken($firstToken, 'reset');
        $this->assertFalse($firstResult, 'First token must be invalidated after a second create()');

        // The second token must still be valid.
        $secondResult = $this->model->findValidByToken($secondToken, 'reset');
        $this->assertIsArray($secondResult, 'Second token must be valid');
    }

    public function test_create_does_not_invalidate_tokens_of_different_type(): void
    {
        // Invitation token for user, then reset token for same user.
        $inviteToken = $this->model->create(self::ADMIN_ID, 'invitation');
        $this->model->create(self::ADMIN_ID, 'reset');

        // The invitation token must still be valid.
        $result = $this->model->findValidByToken($inviteToken, 'invitation');
        $this->assertIsArray($result, 'Invitation token must not be invalidated by a reset create()');
    }

    // -------------------------------------------------------------------------
    // findValidByToken()
    // -------------------------------------------------------------------------

    public function test_findValidByToken_returns_row_for_fresh_token(): void
    {
        $token  = $this->model->create(self::ADMIN_ID, 'reset');
        $result = $this->model->findValidByToken($token, 'reset');

        $this->assertIsArray($result);
        $this->assertSame((string) self::ADMIN_ID, (string) $result['user_id']);
        $this->assertSame('reset', $result['type']);
        $this->assertNull($result['used_at']);
    }

    public function test_findValidByToken_returns_false_when_expired(): void
    {
        $token     = $this->model->create(self::ADMIN_ID, 'reset');
        $tokenHash = hash('sha256', $token);

        // Manually backdate expires_at so the token is already expired.
        $stmt = self::$pdo->prepare(
            "UPDATE password_resets SET expires_at = DATE_SUB(NOW(), INTERVAL 1 SECOND)
             WHERE token_hash = ?"
        );
        $stmt->execute([$tokenHash]);

        $result = $this->model->findValidByToken($token, 'reset');
        $this->assertFalse($result, 'Expired token must not be returned');
    }

    public function test_findValidByToken_returns_false_when_used(): void
    {
        $token  = $this->model->create(self::ADMIN_ID, 'reset');
        $row    = $this->model->findValidByToken($token, 'reset');

        $this->model->markUsed((int) $row['id']);

        $result = $this->model->findValidByToken($token, 'reset');
        $this->assertFalse($result, 'Used token must not be returned');
    }

    public function test_findValidByToken_returns_false_on_type_mismatch(): void
    {
        // Create a 'reset' token but look it up as 'invitation'.
        $token  = $this->model->create(self::ADMIN_ID, 'reset');
        $result = $this->model->findValidByToken($token, 'invitation');

        $this->assertFalse($result, 'Token must not be valid when type does not match');
    }

    public function test_findValidByToken_returns_false_for_nonexistent_token(): void
    {
        $result = $this->model->findValidByToken(str_repeat('a', 64), 'reset');
        $this->assertFalse($result);
    }

    // -------------------------------------------------------------------------
    // markUsed()
    // -------------------------------------------------------------------------

    public function test_markUsed_sets_used_at(): void
    {
        $token = $this->model->create(self::ADMIN_ID, 'reset');
        $row   = $this->model->findValidByToken($token, 'reset');

        $this->assertNull($row['used_at'], 'used_at must be NULL before markUsed()');

        $success = $this->model->markUsed((int) $row['id']);
        $this->assertTrue($success);

        // Read directly from DB to confirm used_at was written.
        $stmt = self::$pdo->prepare(
            "SELECT used_at FROM password_resets WHERE id = ?"
        );
        $stmt->execute([$row['id']]);
        $usedAt = $stmt->fetchColumn();

        $this->assertNotNull($usedAt, 'used_at must be set after markUsed()');
        $this->assertNotFalse($usedAt);
    }
}
