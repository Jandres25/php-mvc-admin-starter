<?php

declare(strict_types=1);

namespace Tests;

use App\Config\Connection;
use Dotenv\Dotenv;
use PDO;
use ReflectionProperty;

abstract class IntegrationTestCase extends TestCase
{
    private static bool $schemaLoaded = false;
    protected static PDO $pdo;

    /**
     * Set to false in subclasses that test methods with their own transaction
     * management (e.g. syncForUser). Those tests reload the seed manually.
     */
    protected bool $useTransactions = true;

    // -------------------------------------------------------------------------
    // One-time setup: load .env.testing, reset singleton, load schema + seed
    // -------------------------------------------------------------------------

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Load .env.testing so Connection picks up the test DB credentials.
        // Must happen before the first Connection::getInstance() call.
        $envFile = BASE_PATH . '/.env.testing';
        if (file_exists($envFile)) {
            $dotenv = Dotenv::createMutable(BASE_PATH, '.env.testing');
            $dotenv->safeLoad();
        }

        // Reset the Connection singleton so it re-reads the freshly loaded env.
        self::resetConnectionSingleton();

        // Store a direct PDO reference for transaction control.
        self::$pdo = Connection::getInstance()->getConnection();

        if (!self::$schemaLoaded) {
            self::loadSchema();
            self::$schemaLoaded = true;
        }
    }

    // -------------------------------------------------------------------------
    // Per-test transaction wrap: every test runs in isolation via rollback
    // -------------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();
        if ($this->useTransactions) {
            self::$pdo->beginTransaction();
        }
    }

    protected function tearDown(): void
    {
        if ($this->useTransactions && self::$pdo->inTransaction()) {
            self::$pdo->rollBack();
        }
        parent::tearDown();
    }

    /**
     * Reload seed data for test classes that opt out of transaction wrapping.
     */
    protected static function reloadSeed(): void
    {
        $seed = BASE_PATH . '/tests/fixtures/sql/minimal_seed.sql';
        foreach (self::parseSql(file_get_contents($seed)) as $sql) {
            self::$pdo->exec($sql);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private static function resetConnectionSingleton(): void
    {
        $prop = new ReflectionProperty(Connection::class, 'instance');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    private static function loadSchema(): void
    {
        $schema = BASE_PATH . '/database/schema.sql';
        $seed   = BASE_PATH . '/tests/fixtures/sql/minimal_seed.sql';

        // schema.sql contains CREATE DATABASE / USE statements — skip them and
        // only run the CREATE TABLE blocks against the test DB directly.
        $schemaSql = file_get_contents($schema);
        $statements = self::parseSql($schemaSql, skipPatterns: [
            '/^CREATE\s+DATABASE/i',
            '/^USE\s+/i',
        ]);

        foreach ($statements as $sql) {
            // Use IF NOT EXISTS so re-runs don't fail if tables already exist
            $sql = preg_replace('/CREATE TABLE\s+/i', 'CREATE TABLE IF NOT EXISTS ', $sql);
            self::$pdo->exec($sql);
        }

        // Load minimal seed (truncates + inserts fresh data)
        $seedSql = file_get_contents($seed);
        foreach (self::parseSql($seedSql) as $sql) {
            self::$pdo->exec($sql);
        }
    }

    /**
     * Splits a SQL file into individual statements, optionally skipping lines
     * that match any of the given regex patterns.
     *
     * @param string   $sql
     * @param string[] $skipPatterns  Regex patterns for lines to exclude
     * @return string[]
     */
    private static function parseSql(string $sql, array $skipPatterns = []): array
    {
        $statements = [];
        $current    = '';

        foreach (explode("\n", $sql) as $line) {
            $trimmed = trim($line);

            // Skip comments and empty lines
            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }

            // Skip lines matching any exclusion pattern
            foreach ($skipPatterns as $pattern) {
                if (preg_match($pattern, $trimmed)) {
                    continue 2;
                }
            }

            $current .= $line . "\n";

            if (str_ends_with($trimmed, ';')) {
                $stmt = trim($current);
                if ($stmt !== '') {
                    $statements[] = $stmt;
                }
                $current = '';
            }
        }

        return $statements;
    }
}
