<?php

namespace App\Models\Traits;

use PDO;
use PDOException;

/**
 * UserStatsTrait
 *
 * Groups aggregation and reporting queries used by the dashboard:
 * totals, active/inactive breakdown, recent registrations, and monthly trend.
 *
 * @package ProyectoBase
 * @subpackage App\Models\Traits
 */
trait UserStatsTrait
{
    /**
     * Returns total, active, inactive and pending user counts.
     *
     * @return array{total: int, active: int, inactive: int, pending: int}
     */
    public function getStatistics()
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS inactive,
                    SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS pending
                FROM {$this->table}
            ");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'total'    => (int) $row['total'],
                'active'   => (int) $row['active'],
                'inactive' => (int) $row['inactive'],
                'pending'  => (int) $row['pending'],
            ];
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return ['total' => 0, 'active' => 0, 'inactive' => 0, 'pending' => 0];
        }
    }

    /**
     * Returns the most recently registered users.
     *
     * @param int $limit Number of users to return
     * @return array
     */
    public function getRecent($limit = 5)
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT id, name, first_surname, email, status, created_at
                FROM {$this->table}
                ORDER BY created_at DESC, id DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Returns active, inactive and pending user counts for the donut chart.
     *
     * @return array{active: int, inactive: int, pending: int}
     */
    public function getUsersByStatus(): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS inactive,
                    SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS pending
                FROM {$this->table}
            ");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'active'   => (int) ($row['active']   ?? 0),
                'inactive' => (int) ($row['inactive']  ?? 0),
                'pending'  => (int) ($row['pending']   ?? 0),
            ];
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return ['active' => 0, 'inactive' => 0, 'pending' => 0];
        }
    }

    /**
     * Returns user registration counts grouped by month for the line chart.
     * Always returns exactly $months entries (zero-filled for empty months).
     *
     * @param int $months Number of months to look back (including current)
     * @return array  Each element: ['ym' => 'YYYY-MM', 'total' => int]
     */
    public function getUsersByMonth(int $months = 6): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS total
                FROM {$this->table}
                WHERE created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL :offset MONTH), '%Y-%m-01')
                GROUP BY ym
                ORDER BY ym ASC
            ");
            $stmt->bindValue(':offset', $months - 1, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Index DB results by 'YYYY-MM'
            $byMonth = [];
            foreach ($rows as $row) {
                $byMonth[$row['ym']] = (int) $row['total'];
            }

            // Build full range, filling gaps with 0
            $result = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $ym = date('Y-m', strtotime("-{$i} months"));
                $result[] = ['ym' => $ym, 'total' => $byMonth[$ym] ?? 0];
            }

            return $result;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }
}
