<?php

namespace Packages\Backup\App\Backup;

use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

/**
 * Handle the Business Logic for Backups.
 */
class BackupService
{
    /**
     * @var EventDispatcher
     */
    protected $event;

    /**
     * @var BackupRepository
     */
    protected $backups;

    /**
     * @param EventDispatcher  $event
     * @param BackupRepository $backups
     */
    public function __construct(
        EventDispatcher $event,
        BackupRepository $backups
    ) {
        $this->event = $event;
        $this->backups = $backups;
    }

    /**
     * @param Source\Source       $source
     * @param Dest\Dest           $dest
     * @param Recurring\Recurring $recurring
     *
     * @return Backup
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
     * @param Backup     $backup
     * @param \Exception $exc
     */
    public function failed(Backup $backup, \Exception $exc)
    {
        $this->event->fire(
            new Events\BackupFailed($backup, $exc)
        );
    }
}
