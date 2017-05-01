<?php

namespace Packages\Backup\App\Archive\Dest\Handler;

use Packages\Backup\App\Archive;

/**
 * Define the requirements for something to be a Backup Dest Handler.
 */
interface Handler
{
    /**
     * @param Archive\Dest\Dest $dest
     * @param string           $tempFile
     * @param string           $destFile
     */
    public function copy(Backup\Dest\Dest $dest, $tempFile, $destFile);

    /**
     * @param Archive\Dest\Dest $dest
     * @param string           $destFile
     */
    public function delete(Backup\Dest\Dest $dest, $destFile);
}
