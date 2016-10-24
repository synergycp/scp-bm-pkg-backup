<?php

namespace Packages\Backup\App\Backup\Field;

use Illuminate\Database\Eloquent\Relations;

/**
 * Define the requirements for something to have Backup Fields.
 */
interface HasValues
{
    /**
     * @return Relations\MorphMany
     */
    public function fields();

    /**
     * @return int
     */
    public function getKey();
}
