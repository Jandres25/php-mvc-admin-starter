<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Models\Role;
use Tests\IntegrationTestCase;

class RoleTest extends IntegrationTestCase
{
    private Role $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Role();
    }

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    public function test_get_all_with_user_count_returns_seeded_roles(): void
    {
        $roles = $this->model->getAllWithUserCount();
        $this->assertCount(2, $roles);
        $this->assertArrayHasKey('total_users', $roles[0]);
    }

    public function test_get_all_with_user_count_ordered_by_name(): void
    {
        $roles = $this->model->getAllWithUserCount();
        $names = array_column($roles, 'name');
        $this->assertSame(['Auditor', 'Editor'], $names);
    }

    public function test_get_by_id_returns_role_row(): void
    {
        $role = $this->model->getById(1);
        $this->assertIsArray($role);
        $this->assertSame('Editor', $role['name']);
    }

    public function test_get_by_id_returns_false_for_unknown_id(): void
    {
        $this->assertFalse($this->model->getById(9999));
    }

    public function test_get_all_active_returns_only_active_roles(): void
    {
        $roles = $this->model->getAllActive();
        $this->assertCount(1, $roles);
        $this->assertSame('Editor', $roles[0]['name']);
    }

    public function test_get_statistics_returns_correct_counts(): void
    {
        $stats = $this->model->getStatistics();
        $this->assertSame(2, $stats['total']);
        $this->assertSame(1, $stats['active']);
        $this->assertSame(1, $stats['inactive']);
    }

    // -------------------------------------------------------------------------
    // nameExists
    // -------------------------------------------------------------------------

    public function test_name_exists_returns_true_for_seeded_name(): void
    {
        $this->assertTrue($this->model->nameExists('Editor'));
    }

    public function test_name_exists_excludes_own_id(): void
    {
        // Checking 'Editor' (id=1) while excluding id=1 should return false
        $this->assertFalse($this->model->nameExists('Editor', 1));
    }

    public function test_name_exists_returns_false_for_unknown_name(): void
    {
        $this->assertFalse($this->model->nameExists('NonExistent'));
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_inserts_new_role(): void
    {
        $result = $this->model->create(['name' => 'Manager', 'description' => 'Team lead']);
        $this->assertTrue($result);

        $id   = $this->model->getLastInsertId();
        $role = $this->model->getById($id);
        $this->assertSame('Manager', $role['name']);
        $this->assertSame('Team lead', $role['description']);
        $this->assertSame(1, (int) $role['status']);
    }

    public function test_create_with_null_description(): void
    {
        $result = $this->model->create(['name' => 'Viewer', 'description' => '']);
        $this->assertTrue($result);

        $role = $this->model->getById($this->model->getLastInsertId());
        $this->assertNull($role['description']);
    }

    public function test_create_rejects_duplicate_name(): void
    {
        $result = $this->model->create(['name' => 'Editor', 'description' => '']);
        $this->assertFalse($result);
        $this->assertNotEmpty($this->model->getLastError());
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_changes_name_and_description(): void
    {
        $result = $this->model->update(1, ['name' => 'Senior Editor', 'description' => 'Updated']);
        $this->assertTrue($result);

        $role = $this->model->getById(1);
        $this->assertSame('Senior Editor', $role['name']);
        $this->assertSame('Updated', $role['description']);
    }

    public function test_update_rejects_duplicate_name_from_another_role(): void
    {
        // Try to rename role 1 (Editor) to 'Auditor' (already taken by role 2)
        $result = $this->model->update(1, ['name' => 'Auditor', 'description' => '']);
        $this->assertFalse($result);
    }

    public function test_update_allows_same_name_for_own_id(): void
    {
        // Keeping the same name should pass the uniqueness check
        $result = $this->model->update(1, ['name' => 'Editor', 'description' => 'New desc']);
        $this->assertTrue($result);
    }

    // -------------------------------------------------------------------------
    // updateStatus
    // -------------------------------------------------------------------------

    public function test_update_status_deactivates_active_role(): void
    {
        $result = $this->model->updateStatus(1, 0);
        $this->assertTrue($result);

        $role = $this->model->getById(1);
        $this->assertSame(0, (int) $role['status']);
    }

    public function test_update_status_activates_inactive_role(): void
    {
        $result = $this->model->updateStatus(2, 1);
        $this->assertTrue($result);

        $role = $this->model->getById(2);
        $this->assertSame(1, (int) $role['status']);
    }

    // -------------------------------------------------------------------------
    // countUsers
    // -------------------------------------------------------------------------

    public function test_count_users_returns_zero_when_no_users_assigned(): void
    {
        $this->assertSame(0, $this->model->countUsers(1));
    }

    public function test_count_users_returns_correct_count_after_assignment(): void
    {
        // Assign role 1 to user 2 directly via PDO
        self::$pdo->exec('UPDATE users SET role_id = 1 WHERE id = 2');

        $this->assertSame(1, $this->model->countUsers(1));
    }
}
