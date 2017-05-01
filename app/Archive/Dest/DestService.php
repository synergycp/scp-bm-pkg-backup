<?php

namespace Packages\Backup\App\Archive\Dest;

use Packages\Backup\App\Archive;
use Illuminate\Contracts\Events;

/**
 * Handle the Business Logic between a Backup File and its Destination.
 */
class DestService
{
    /**
     * @var Archive\ArchiveService
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
     * @var Archive\File\FileService
     */
    protected $file;

    /**
     * @param Events\Dispatcher       $event
     * @param Archive\ArchiveService    $backup
     * @param Handler\HandlerService  $handler
     * @param Archive\File\FileService $file
     */
    public function __construct(
        Events\Dispatcher $event,
        Archive\ArchiveService $backup,
        Handler\HandlerService $handler,
        Archive\File\FileService $file
    ) {
        $this->file = $file;
        $this->event = $event;
        $this->backup = $backup;
        $this->handler = $handler;
    }

    /**
     * Copy the Backup to the Destination.
     *
     * @param Archive\Archive $backup
     *
     * @throws CopyToDestFailed
     */
    public function copy(Backup\Archive $backup)
    {
        try {
            $this->event->fire(
                new CopyingArchiveToDest($backup)
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
                new CopiedArchiveToDest($backup)
            );
        } catch (\Exception $exc) {
            $this->backup->failed($backup, $exc);

            throw new CopyToDestFailed($exc);
        }
    }

    /**
     * Delete the Backup off of the Destination.
     *
     * @param Archive\Archive $backup
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
