<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Models\User;
use Tests\IntegrationTestCase;

class UserTest extends IntegrationTestCase
{
    private User $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new User();
    }

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    public function test_get_by_id_returns_user_row(): void
    {
        $user = $this->model->getById(1);

        $this->assertIsArray($user);
        $this->assertSame('1', (string) $user['id']);
        $this->assertSame('admin@test.com', $user['email']);
    }

    public function test_get_by_id_returns_false_for_missing_id(): void
    {
        $this->assertFalse($this->model->getById(9999));
    }

    public function test_get_all_returns_seeded_users(): void
    {
        $users = $this->model->getAll();
        $this->assertCount(2, $users);
    }

    public function test_email_exists_returns_true_for_seeded_email(): void
    {
        $this->assertTrue($this->model->emailExists('admin@test.com'));
    }

    public function test_email_exists_returns_false_for_unknown_email(): void
    {
        $this->assertFalse($this->model->emailExists('nobody@test.com'));
    }

    public function test_email_exists_excludes_own_id(): void
    {
        // Checking admin's own email while excluding admin's ID must return false
        $this->assertFalse($this->model->emailExists('admin@test.com', 1));
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_inserts_row_and_new_row_is_findable(): void
    {
        $result = $this->model->create([
            'name'            => 'Jane',
            'first_surname'   => 'Doe',
            'second_surname'  => null,
            'document_type'   => 'DNI',
            'document_number' => '99999999',
            'address'         => null,
            'phone'           => null,
            'email'           => 'jane@test.com',
            'password'        => 'plainpassword',
            'image'           => null,
            'status'          => 1,
        ]);

        $this->assertTrue($result);

        $id   = $this->model->getLastInsertId();
        $user = $this->model->getById((int) $id);
        $this->assertSame('Jane', $user['name']);
        $this->assertSame('jane@test.com', $user['email']);
    }

    public function test_create_hashes_password(): void
    {
        $this->model->create([
            'name'            => 'Hash',
            'first_surname'   => 'Test',
            'second_surname'  => null,
            'document_type'   => 'DNI',
            'document_number' => '88888888',
            'address'         => null,
            'phone'           => null,
            'email'           => 'hash@test.com',
            'position'        => 'editor',
            'password'        => 'mypassword',
            'image'           => null,
            'status'          => 1,
        ]);

        $id   = $this->model->getLastInsertId();
        $user = $this->model->getById((int) $id);
        $this->assertTrue(password_verify('mypassword', $user['password']));
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_modifies_persisted_fields(): void
    {
        $result = $this->model->update(2, [
            'name'            => 'Updated',
            'first_surname'   => 'User',
            'second_surname'  => null,
            'document_type'   => 'DNI',
            'document_number' => '00000002',
            'address'         => '123 Street',
            'phone'           => '5551234',
            'email'           => 'user@test.com',
            'position'        => 'editor',
            'image'           => null,
            'status'          => 1,
        ]);

        $this->assertTrue($result);
        $user = $this->model->getById(2);
        $this->assertSame('Updated', $user['name']);
        $this->assertSame('123 Street', $user['address']);
    }

    public function test_update_status_changes_active_flag(): void
    {
        $this->assertTrue($this->model->updateStatus(2, 0));
        $this->assertSame('0', (string) $this->model->getStatusById(2));

        $this->assertTrue($this->model->updateStatus(2, 1));
        $this->assertSame('1', (string) $this->model->getStatusById(2));
    }

    // -------------------------------------------------------------------------
    // Remember-me token
    // -------------------------------------------------------------------------

    public function test_set_remember_token_persists_hash_and_expiry(): void
    {
        $hash    = hash('sha256', 'test_token');
        $expires = date('Y-m-d H:i:s', time() + 3600);

        $result = $this->model->setRememberToken(2, $hash, $expires);

        $this->assertTrue($result);
        $row = $this->model->getById(2);
        $this->assertSame($hash, $row['remember_token']);
    }

    public function test_find_by_remember_token_returns_user_for_valid_token(): void
    {
        $hash    = hash('sha256', 'valid_token');
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $this->model->setRememberToken(2, $hash, $expires);

        $user = $this->model->findByRememberToken($hash);

        $this->assertIsArray($user);
        $this->assertSame('user@test.com', $user['email']);
    }

    public function test_find_by_remember_token_returns_false_for_expired_token(): void
    {
        $hash    = hash('sha256', 'expired_token');
        $expires = date('Y-m-d H:i:s', time() - 1); // already expired
        $this->model->setRememberToken(2, $hash, $expires);

        $this->assertFalse($this->model->findByRememberToken($hash));
    }

    public function test_find_by_remember_token_returns_false_for_unknown_hash(): void
    {
        $this->assertFalse($this->model->findByRememberToken('nonexistent_hash'));
    }

    public function test_clear_remember_token_removes_stored_token(): void
    {
        $hash    = hash('sha256', 'clear_me');
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $this->model->setRememberToken(2, $hash, $expires);

        $this->model->clearRememberToken(2);

        $row = $this->model->getById(2);
        $this->assertNull($row['remember_token']);
    }

    // -------------------------------------------------------------------------
    // Permissions timestamp
    // -------------------------------------------------------------------------

    public function test_get_permissions_timestamp_returns_null_when_never_set(): void
    {
        // Seeded users have no permissions_updated_at
        $this->assertNull($this->model->getPermissionsTimestamp(1));
    }

    public function test_update_permissions_timestamp_persists_a_value(): void
    {
        $this->model->updatePermissionsTimestamp(1);
        $ts = $this->model->getPermissionsTimestamp(1);

        $this->assertNotNull($ts);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}/', $ts);
    }

    public function test_update_permissions_timestamp_bumps_existing_value(): void
    {
        $this->model->updatePermissionsTimestamp(1);
        $first = $this->model->getPermissionsTimestamp(1);

        sleep(1);
        $this->model->updatePermissionsTimestamp(1);
        $second = $this->model->getPermissionsTimestamp(1);

        $this->assertGreaterThanOrEqual($first, $second);
    }
}
