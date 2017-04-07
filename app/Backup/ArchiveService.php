<?php

namespace Packages\Backup\App\Backup;

use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

/**
 * Handle the Business Logic for Backups.
 */
class ArchiveService
{
    /**
     * @var EventDispatcher
     */
    protected $event;

    /**
     * @var ArchiveRepository
     */
    protected $backups;

    /**
     * @param EventDispatcher  $event
     * @param ArchiveRepository $backups
     */
    public function __construct(
        EventDispatcher $event,
        ArchiveRepository $backups
    ) {
        $this->event = $event;
        $this->backups = $backups;
    }

    /**
     * @param Source\Source       $source
     * @param Dest\Dest           $dest
     * @param Recurring\Recurring $recurring
     *
     * @return Archive
     */
    public function create(
        Source\Source $source,
        Dest\Dest $dest,
        Recurring\Recurring $recurring = null
    ) {
        $backup = $this->backups->make();
        $backup->source()->associate($source);
        $backup->dest()->associate($dest);
        $backup->recurring()->associate(
            $recurring ? $recurring->id : null
        );
        $backup->save();

        $this->event->fire(
            new Events\BackupCreated($backup)
        );

        return $backup;
    }

    /**
     * @param Archive     $backup
     * @param \Exception $exc
     */
    public function failed(Archive $backup, \Exception $exc)
    {
        $this->event->fire(
            new Events\BackupFailed($backup, $exc)
        );
    }
}
