<?php

namespace Packages\Backup\App\Archive\Handler;

/**
 * Define the storage of Handler Types in the Database.
 */
interface HandlerType
{
    /**
     * @var int
     */
    const SOURCE = 0;

    /**
     * @var int
     */
    const DEST = 1;
}
