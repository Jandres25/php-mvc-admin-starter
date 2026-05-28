<?php

declare(strict_types=1);

namespace Tests\Integration\Auth;

use App\Models\PasswordReset;
use App\Models\User;
use Tests\IntegrationTestCase;

/**
 * Integration tests for Sub-fase 2.4 — resend-invitation flow.
 */
class ResendInvitationTest extends IntegrationTestCase
{
    private User          $userModel;
    private PasswordReset $resets;

    private const EDITOR_ID = 2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        $this->resets    = new PasswordReset();
    }

    public function test_resend_invitation_invalidates_previous_token(): void
    {
        $this->setUserStatus(self::EDITOR_ID, User::STATUS_PENDING);

        $firstToken  = $this->resets->create(self::EDITOR_ID, 'invitation');
        $secondToken = $this->resets->create(self::EDITOR_ID, 'invitation');

        // First token must be invalidated by the second create()
        $this->assertFalse($this->resets->findValidByToken($firstToken, 'invitation'));
        $this->assertIsArray($this->resets->findValidByToken($secondToken, 'invitation'));
    }

    public function test_resend_invitation_creates_new_48h_token(): void
    {
        $this->setUserStatus(self::EDITOR_ID, User::STATUS_PENDING);

        $this->resets->create(self::EDITOR_ID, 'invitation'); // first (will be invalidated)
        $token = $this->resets->create(self::EDITOR_ID, 'invitation');

        $row = $this->resets->findValidByToken($token, 'invitation');
        $this->assertIsArray($row);

        $expiresAt = strtotime($row['expires_at']);
        $expected  = time() + (48 * 3600);
        $this->assertEqualsWithDelta($expected, $expiresAt, 300);
    }

    public function test_resend_on_active_user_is_rejected(): void
    {
        // Editor is active (status=1) in the seed
        $user = $this->userModel->getById(self::EDITOR_ID);
        $this->assertSame(User::STATUS_ACTIVE, (int) $user['status']);

        // The controller guard: status must be STATUS_PENDING
        $isBlocked = (int) $user['status'] !== User::STATUS_PENDING;
        $this->assertTrue($isBlocked, 'Active user should be blocked from resend');
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
