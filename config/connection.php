<?php

namespace Config;

require_once __DIR__ . '/../app/config/Connection.php';

// Legacy alias. Prefer App\Config\Connection.
if (!class_exists(__NAMESPACE__ . '\\Connection', false)) {
    class_alias(\App\Config\Connection::class, __NAMESPACE__ . '\\Connection');
}
