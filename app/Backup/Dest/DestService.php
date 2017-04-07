<?php

namespace Packages\Backup\App\Backup\Dest;

use Packages\Backup\App\Backup;
use Illuminate\Contracts\Events;

/**
 * Handle the Business Logic between a Backup File and its Destination.
 */
class DestService
{
    /**
     * @var Backup\ArchiveService
     */
    protected $backup;

    /**
     * @var Handler\HandlerService
     */
    protected $handler;

    /**
     * @var Events\Dispatcher
     */
    protected $event;

    /**
     * @var Backup\File\FileService
     */
    protected $file;

    /**
     * @param Events\Dispatcher       $event
     * @param Backup\ArchiveService    $backup
     * @param Handler\HandlerService  $handler
     * @param Backup\File\FileService $file
     */
    public function __construct(
        Events\Dispatcher $event,
        Backup\ArchiveService $backup,
        Handler\HandlerService $handler,
        Backup\File\FileService $file
    ) {
        $this->file = $file;
        $this->event = $event;
        $this->backup = $backup;
        $this->handler = $handler;
    }

    /**
     * Copy the Backup to the Destination.
     *
     * @param Backup\Archive $backup
     *
     * @throws CopyToDestFailed
     */
    public function copy(Backup\Archive $backup)
    {
        try {
            $this->event->fire(
                new CopyingBackupToDest($backup)
            );

            $tempFile = $this->file->tempFile($backup);
            $destFile = sprintf(
                '%s.%d.%s',
                str_slug($backup->source->name),
                $backup->id,
                $backup->source->ext
            );

            $this->handler
                ->get($backup->dest)
                ->copy($backup->dest, $tempFile, $destFile)
                ;

            $this->event->fire(
                new CopiedBackupToDest($backup)
            );
        } catch (\Exception $exc) {
            $this->backup->failed($backup, $exc);

            throw new CopyToDestFailed($exc);
        }
    }

    /**
     * Delete the Backup off of the Destination.
     *
     * @param Backup\Archive $backup
     *
     * @throws DeleteFromDestFailed
     */
    public function delete(Backup\Archive $backup)
    {
        try {
            $this->handler
                ->get($backup->dest)
                ->delete($backup)
                ;
        } catch (\Exception $exc) {
            $this->backup->failed($backup, $exc);

            throw new DeleteFromDestFailed($exc);
        }
    }
}
