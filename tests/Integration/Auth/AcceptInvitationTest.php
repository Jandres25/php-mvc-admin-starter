<?php

declare(strict_types=1);

namespace Tests\Integration\Auth;

use App\Models\PasswordReset;
use App\Models\User;
use Tests\IntegrationTestCase;

/**
 * Integration tests for Sub-fase 2.3 — accept-invitation flow.
 *
 * Tests the model/data layer that backs InvitationController:
 * token validation, password set, user activation, token consumed.
 */
class AcceptInvitationTest extends IntegrationTestCase
{
    private User          $userModel;
    private PasswordReset $resets;

    private const EDITOR_ID    = 2;
    private const EDITOR_EMAIL = 'user@test.com';

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        $this->resets    = new PasswordReset();
    }

    // -------------------------------------------------------------------------
    // Happy path
    // -------------------------------------------------------------------------

    public function test_accept_invitation_with_valid_token_activates_user_and_sets_password(): void
    {
        $this->setUserStatus(self::EDITOR_ID, User::STATUS_PENDING);

        $token = $this->resets->create(self::EDITOR_ID, 'invitation');
        $row   = $this->resets->findValidByToken($token, 'invitation');

        $this->assertIsArray($row);

        // Simulate the controller: reset password + activate
        $this->assertTrue($this->userModel->resetPassword(self::EDITOR_ID, 'newPassword1'));
        $this->assertTrue($this->userModel->activate(self::EDITOR_ID));
        $this->resets->markUsed((int) $row['id']);

        $user = $this->userModel->getById(self::EDITOR_ID);
        $this->assertSame(User::STATUS_ACTIVE, (int) $user['status']);
        $this->assertTrue(password_verify('newPassword1', $user['password']));
    }

    public function test_accept_invitation_marks_token_used(): void
    {
        $token = $this->resets->create(self::EDITOR_ID, 'invitation');
        $row   = $this->resets->findValidByToken($token, 'invitation');

        $this->resets->markUsed((int) $row['id']);

        $this->assertFalse($this->resets->findValidByToken($token, 'invitation'));
    }

    // -------------------------------------------------------------------------
    // Token rejection cases
    // -------------------------------------------------------------------------

    public function test_accept_invitation_reused_token_fails(): void
    {
        $token = $this->resets->create(self::EDITOR_ID, 'invitation');
        $row   = $this->resets->findValidByToken($token, 'invitation');
        $this->resets->markUsed((int) $row['id']);

        // Second attempt with same token must fail
        $this->assertFalse($this->resets->findValidByToken($token, 'invitation'));
    }

    public function test_accept_invitation_expired_48h_token_fails(): void
    {
        $token = $this->resets->create(self::EDITOR_ID, 'invitation');

        // Force-expire the token
        self::$pdo->prepare(
            "UPDATE password_resets SET expires_at = DATE_SUB(NOW(), INTERVAL 1 SECOND) WHERE token_hash = ?"
        )->execute([hash('sha256', $token)]);

        $this->assertFalse($this->resets->findValidByToken($token, 'invitation'));
    }

    public function test_reset_token_cannot_be_used_as_invitation_token(): void
    {
        $token = $this->resets->create(self::EDITOR_ID, 'reset');

        // type mismatch — must be rejected
        $this->assertFalse($this->resets->findValidByToken($token, 'invitation'));
        // but valid as reset
        $this->assertIsArray($this->resets->findValidByToken($token, 'reset'));
    }

    // -------------------------------------------------------------------------
    // Cross-flow: accepted user can log in
    // -------------------------------------------------------------------------

    public function test_accepted_user_can_then_login(): void
    {
        $this->setUserStatus(self::EDITOR_ID, User::STATUS_PENDING);

        $token = $this->resets->create(self::EDITOR_ID, 'invitation');
        $row   = $this->resets->findValidByToken($token, 'invitation');

        $this->userModel->resetPassword(self::EDITOR_ID, 'loginReady1');
        $this->userModel->activate(self::EDITOR_ID);
        $this->resets->markUsed((int) $row['id']);

        $user = $this->userModel->getById(self::EDITOR_ID);

        $this->assertSame(User::STATUS_ACTIVE, (int) $user['status']);
        $this->assertTrue(password_verify('loginReady1', $user['password']));
        // Pending guard would NOT block this user now
        $this->assertNotSame(User::STATUS_PENDING, (int) $user['status']);
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function setUserStatus(int $userId, int $status): void
    {
        self::$pdo->prepare("UPDATE users SET status = ? WHERE id = ?")
                  ->execute([$status, $userId]);
    }
}
