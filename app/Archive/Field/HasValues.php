<?php

namespace Packages\Backup\App\Archive\Field;

use Illuminate\Database\Eloquent\Relations;

/**
 * Define the requirements for something to have Backup Fields.
 */
interface HasValues {
  /**
   * @return Relations\MorphMany
   */
  public function fieldValues();

  /**
   * @return int
   */
  public function getKey();
}
