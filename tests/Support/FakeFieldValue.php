<?php

namespace Packages\Backup\Tests\Support;

class FakeFieldValue {
  public function __construct(private $value) {
  }

  public function value() {
    return $this->value;
  }
}
