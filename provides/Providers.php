<?php

namespace Packages\Backup\App;

/**
 * Define Service Providers in order of when they are loaded.
 */

return [
    Archive\ArchiveServiceProvider::class,
    Recurring\RecurringServiceProvider::class,
];
