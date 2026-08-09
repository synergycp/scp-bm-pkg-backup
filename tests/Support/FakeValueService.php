<?php

namespace Packages\Backup\Tests\Support;

use Packages\Backup\App\Archive\Field\HasValues;
use Packages\Backup\App\Archive\Field\ValueService;

/**
 * In-memory Field values, keyed by field name.
 */
class FakeValueService extends ValueService {
  public function __construct(public array $map = []) {
  }

  public function all(HasValues $hasValues) {
    return new FakeFieldValues($this->map);
  }

  public function byName(HasValues $hasFields, $name) {
    return new FakeFieldValue($this->map[$name] ?? null);
  }
}
