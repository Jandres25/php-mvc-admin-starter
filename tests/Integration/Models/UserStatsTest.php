<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Models\User;
use Tests\IntegrationTestCase;

class UserStatsTest extends IntegrationTestCase
{
    private User $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new User();
    }

    public function test_getStatistics_includes_pending_count(): void
    {
        self::$pdo->exec(
            "INSERT INTO users (name, first_surname, document_type, document_number, email, password, role_id, status)
             VALUES ('Pending', 'User', 'CC', '11111111', 'pending@test.com', 'x', 1, 2)"
        );

        $stats = $this->model->getStatistics();

        $this->assertArrayHasKey('pending', $stats);
        $this->assertGreaterThanOrEqual(1, $stats['pending']);
    }

    public function test_getUsersByStatus_includes_pending_count(): void
    {
        self::$pdo->exec(
            "INSERT INTO users (name, first_surname, document_type, document_number, email, password, role_id, status)
             VALUES ('Pending2', 'User', 'CC', '22222222', 'pending2@test.com', 'x', 1, 2)"
        );

        $counts = $this->model->getUsersByStatus();

        $this->assertArrayHasKey('pending', $counts);
        $this->assertGreaterThanOrEqual(1, $counts['pending']);
    }
}
