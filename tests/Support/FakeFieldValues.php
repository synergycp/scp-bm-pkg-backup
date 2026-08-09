<?php

namespace Packages\Backup\Tests\Support;

class FakeFieldValues {
  public function __construct(private array $map) {
  }

  public function value($name) {
    return $this->map[$name] ?? null;
  }
}
