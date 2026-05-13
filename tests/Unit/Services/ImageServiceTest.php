<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\ImageService;
use Tests\TestCase;

class ImageServiceTest extends TestCase
{
    private string $uploadDir;
    private ImageService $service;
    private static string $fixturesDir;

    public static function setUpBeforeClass(): void
    {
        self::$fixturesDir = BASE_PATH . '/tests/fixtures/images/';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->uploadDir = sys_get_temp_dir() . '/imgservice_test_' . uniqid() . '/';
        $this->service   = new ImageService($this->uploadDir);
    }

    protected function tearDown(): void
    {
        // Clean up upload dir created by the service
        if (is_dir($this->uploadDir)) {
            foreach (glob($this->uploadDir . '*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($this->uploadDir);
        }
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // deleteImage()
    // -------------------------------------------------------------------------

    public function test_delete_image_removes_existing_file(): void
    {
        $path = $this->uploadDir . 'to_delete.jpg';
        copy(self::$fixturesDir . 'sample.jpg', $path);
        $this->assertTrue(file_exists($path));

        $result = $this->service->deleteImage('to_delete.jpg');

        $this->assertTrue($result);
        $this->assertFalse(file_exists($path));
    }

    public function test_delete_image_returns_true_for_already_missing_file(): void
    {
        $result = $this->service->deleteImage('nonexistent.jpg');
        $this->assertTrue($result);
    }

    public function test_delete_image_never_deletes_default_image(): void
    {
        // Place a file named user_default.jpg in uploadDir
        $path = $this->uploadDir . 'user_default.jpg';
        copy(self::$fixturesDir . 'sample.jpg', $path);

        $result = $this->service->deleteImage('user_default.jpg');

        $this->assertTrue($result);
        $this->assertTrue(file_exists($path), 'user_default.jpg must never be deleted');
    }

    public function test_delete_image_returns_true_for_empty_filename(): void
    {
        $result = $this->service->deleteImage('');
        $this->assertTrue($result);
    }

    // -------------------------------------------------------------------------
    // processImage() — validation path (no HTTP upload needed)
    // -------------------------------------------------------------------------

    private function buildFileEntry(
        string $path,
        string $mime,
        int $error = UPLOAD_ERR_OK,
        ?int $size = null
    ): array {
        return [
            'name'     => basename($path),
            'type'     => $mime,
            'tmp_name' => $path,
            'error'    => $error,
            'size'     => $size ?? filesize($path),
        ];
    }

    public function test_process_image_rejects_upload_error_code(): void
    {
        $entry = $this->buildFileEntry(
            self::$fixturesDir . 'sample.jpg',
            'image/jpeg',
            UPLOAD_ERR_PARTIAL
        );
        $this->assertFalse($this->service->processImage($entry));
    }

    public function test_process_image_rejects_invalid_mime(): void
    {
        $entry = $this->buildFileEntry(
            self::$fixturesDir . 'corrupt.txt',
            'text/plain'
        );
        $this->assertFalse($this->service->processImage($entry));
    }

    public function test_process_image_rejects_oversize_file(): void
    {
        $entry = $this->buildFileEntry(
            self::$fixturesDir . 'sample.jpg',
            'image/jpeg',
            UPLOAD_ERR_OK,
            6 * 1024 * 1024  // 6 MB — over the 5 MB limit
        );
        $this->assertFalse($this->service->processImage($entry));
    }

    public function test_process_image_rejects_null_input(): void
    {
        $this->assertFalse($this->service->processImage(null));
    }

    // -------------------------------------------------------------------------
    // resizeImage()
    // -------------------------------------------------------------------------

    public function test_resize_image_returns_false_for_missing_file(): void
    {
        $result = $this->service->resizeImage('/tmp/no_such_file.jpg');
        $this->assertFalse($result);
    }

    public function test_resize_image_returns_false_for_non_image_file(): void
    {
        $result = $this->service->resizeImage(self::$fixturesDir . 'corrupt.txt');
        $this->assertFalse($result);
    }

    public function test_resize_jpeg_produces_correct_dimensions(): void
    {
        $dest = $this->uploadDir . 'resized.jpg';
        copy(self::$fixturesDir . 'sample.jpg', $dest);

        $result = $this->service->resizeImage($dest, 30, 30);

        $this->assertTrue($result);
        [$w, $h] = getimagesize($dest);
        // sample.jpg is 50x50 (square) — after resize to 30x30 both dims ≤ 30
        $this->assertLessThanOrEqual(30, $w);
        $this->assertLessThanOrEqual(30, $h);
    }

    public function test_resize_preserves_aspect_ratio_for_non_square(): void
    {
        // sample.png is 80x40 (2:1 ratio)
        $dest = $this->uploadDir . 'resized.png';
        copy(self::$fixturesDir . 'sample.png', $dest);

        $this->service->resizeImage($dest, 40, 40);

        [$w, $h] = getimagesize($dest);
        // After resize to 40x40 target, aspect ratio must be preserved (≈ 2:1)
        $this->assertGreaterThan(0, $h);
        $ratio = $w / $h;
        $this->assertEqualsWithDelta(2.0, $ratio, 0.1);
    }
}
