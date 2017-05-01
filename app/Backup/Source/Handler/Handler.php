<?php

namespace Packages\Backup\App\Backup\Source\Handler;

use Packages\Backup\App\Backup;

/**
 * Define the requirements for something to be a BackupHandler.
 */
interface Handler
{
    /**
     * @param Backup\Archive $backup
     * @param string        $tempFile
     */
    public function handle(Backup\Archive $backup, $tempFile);
}
