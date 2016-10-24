<?php

namespace Packages\Backup\App\Backup\Dest\Handler;

use Packages\Backup\App\Backup;

/**
 * Define the requirements for something to be a Backup Dest Handler.
 */
interface Handler
{
    /**
     * @param Backup\Dest\Dest $dest
     * @param string           $tempFile
     * @param string           $destFile
     */
    public function copy(Backup\Dest\Dest $dest, $tempFile, $destFile);

    /**
     * @param Backup\Dest\Dest $dest
     * @param string           $destFile
     */
    public function delete(Backup\Dest\Dest $dest, $destFile);
}
