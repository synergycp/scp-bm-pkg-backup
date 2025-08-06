<?php

namespace Packages\Backup\App\Archive;

use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Packages\Backup\App\Recurring;

/**
 * Handle the Business Logic for Backups.
 */
class ArchiveService {
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
    /** @var Archive $backup */
    $backup = $this->backups->make();
    $backup->source()->associate($source);
    $backup->dest()->associate($dest);
    $backup->recurring()->associate($recurring ? $recurring->getKey() : null);
    $backup->save();

    $this->event->dispatch(new Events\ArchiveCreated($backup));

    return $backup;
  }

  /**
   * @param Archive     $backup
   * @param \Exception $exc
   */
  public function failed(Archive $backup, \Exception $exc) {
    $this->event->dispatch(new Events\ArchiveFailed($backup, $exc));
  }
}
