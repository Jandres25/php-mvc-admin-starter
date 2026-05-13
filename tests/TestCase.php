<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionMethod;

abstract class TestCase extends BaseTestCase
{
    private array $serverBackup = [];

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        $_POST    = [];
        $_GET     = [];
        $_COOKIE  = [];

        $this->serverBackup = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
        $_SESSION = [];
        $_COOKIE  = [];

        parent::tearDown();
    }

    /**
     * Calls a private or protected method via reflection.
     *
     * @param object $object
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    protected function invokePrivate(object $object, string $method, array $args = []): mixed
    {
        $ref = new ReflectionMethod($object, $method);
        $ref->setAccessible(true);
        return $ref->invokeArgs($object, $args);
    }
}
