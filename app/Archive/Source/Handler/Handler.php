<?php

namespace Packages\Backup\App\Archive\Source\Handler;

use Packages\Backup\App\Archive;

/**
 * Define the requirements for something to be a BackupHandler.
 */
interface Handler
{
    /**
     * @param Archive\Archive $backup
     * @param string        $tempFile
     */
    public function handle(Archive\Archive $backup, $tempFile);
}
