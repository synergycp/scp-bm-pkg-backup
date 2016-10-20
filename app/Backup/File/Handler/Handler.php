<?php

namespace Packages\Backup\App\Backup\File\Handler;

use Packages\Backup\App\Backup;

/**
 * Define the requirements for something to be a BackupHandler.
 */
interface Handler
{
    /**
     * @param Backup\Backup $backup
     * @param string        $tempFile
     */
    public function handle(Backup\Backup $backup, $tempFile);
}
