<?php

namespace Packages\Backup\Tests\Support;

/**
 * Backing store for the config() helper stubbed in tests/bootstrap.php.
 */
final class TestConfig {
  public static array $values = [];

  public static function get(string $key, $default = null) {
    return self::$values[$key] ?? $default;
  }
}
