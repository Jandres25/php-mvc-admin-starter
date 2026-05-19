<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\DashboardCache;
use Tests\TestCase;

class DashboardCacheTest extends TestCase
{
    // --- get / put ---

    public function test_get_returns_null_when_key_missing(): void
    {
        $this->assertNull(DashboardCache::get('user_stats'));
    }

    public function test_put_and_get_returns_stored_data(): void
    {
        $data = ['total' => 5, 'active' => 3];
        DashboardCache::put('user_stats', $data);

        $this->assertSame($data, DashboardCache::get('user_stats'));
    }

    public function test_get_returns_null_when_entry_expired(): void
    {
        $data = ['total' => 5];
        DashboardCache::put('user_stats', $data);

        // Manually backdate the stored_at to simulate expiry
        $_SESSION['dashboard_cache']['user_stats']['stored_at'] = time() - DashboardCache::ttl() - 1;

        $this->assertNull(DashboardCache::get('user_stats'));
    }

    public function test_get_returns_data_within_ttl(): void
    {
        $data = ['total' => 10];
        DashboardCache::put('user_stats', $data);

        // Backdate by (TTL - 1) — still valid
        $_SESSION['dashboard_cache']['user_stats']['stored_at'] = time() - DashboardCache::ttl() + 1;

        $this->assertSame($data, DashboardCache::get('user_stats'));
    }

    // --- remember ---

    public function test_remember_calls_loader_on_miss(): void
    {
        $called = false;
        $result = DashboardCache::remember('perm_stats', function () use (&$called) {
            $called = true;
            return ['total' => 2];
        });

        $this->assertTrue($called);
        $this->assertSame(['total' => 2], $result);
    }

    public function test_remember_skips_loader_on_hit(): void
    {
        DashboardCache::put('perm_stats', ['total' => 2]);

        $called = false;
        $result = DashboardCache::remember('perm_stats', function () use (&$called) {
            $called = true;
            return ['total' => 99];
        });

        $this->assertFalse($called);
        $this->assertSame(['total' => 2], $result);
    }

    // --- forget ---

    public function test_forget_removes_single_key(): void
    {
        DashboardCache::put('user_stats', ['total' => 5]);
        DashboardCache::put('perm_stats', ['total' => 3]);

        DashboardCache::forget('user_stats');

        $this->assertNull(DashboardCache::get('user_stats'));
        $this->assertSame(['total' => 3], DashboardCache::get('perm_stats'));
    }

    public function test_forget_on_nonexistent_key_does_not_throw(): void
    {
        $this->expectNotToPerformAssertions();
        DashboardCache::forget('nonexistent_key');
    }

    // --- flush ---

    public function test_flush_clears_all_keys(): void
    {
        DashboardCache::put('user_stats', ['total' => 5]);
        DashboardCache::put('perm_stats', ['total' => 3]);
        DashboardCache::put('recent_users', [['id' => 1]]);

        DashboardCache::flush();

        $this->assertNull(DashboardCache::get('user_stats'));
        $this->assertNull(DashboardCache::get('perm_stats'));
        $this->assertNull(DashboardCache::get('recent_users'));
        $this->assertArrayNotHasKey('dashboard_cache', $_SESSION);
    }

    // --- ttl ---

    public function test_ttl_returns_default_when_env_not_set(): void
    {
        // TestCase::setUp clears $_SESSION but not putenv — default is 300
        $this->assertGreaterThan(0, DashboardCache::ttl());
    }

    public function test_ttl_reads_env_override(): void
    {
        putenv('DASHBOARD_CACHE_TTL=120');

        $this->assertSame(120, DashboardCache::ttl());

        putenv('DASHBOARD_CACHE_TTL'); // restore
    }
}
