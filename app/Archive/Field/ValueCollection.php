<?php

namespace Packages\Backup\App\Archive\Field;

use Illuminate\Database\Eloquent\Collection;

class ValueCollection extends Collection
{
  /**
   * @param string $name
   *
   * @return Value
   */
  public function byName($name)
  {
    $matchesName = function (Value $value) use (&$name) {
      return $value->field->name === $name;
    };

    return $this->load('field')->first($matchesName);
  }

  /**
   * @param string $name
   *
   * @return string
   */
  public function value($key, $_ = null)
  {
    return $this->byName($key)->value();
  }
}
