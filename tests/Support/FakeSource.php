<?php

namespace Packages\Backup\Tests\Support;

use Packages\Backup\App\Archive\Field\HasValues;

class FakeSource implements HasValues {
  public function fieldValues() {
  }

  public function getKey() {
    return 1;
  }
}
