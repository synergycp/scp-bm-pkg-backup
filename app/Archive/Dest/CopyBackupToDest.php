<?php

namespace Packages\Backup\App\Archive\Dest;

use Packages\Backup\App\Archive;

/**
 * Copy a Backup from its temp file to its destination.
 *
 * DO NOT QUEUE THIS: it needs to be run on the same worker that originally created the backup.
 */
class CopyBackupToDest {
  /**
   * @var DestService
   */
  protected $service;

  /**
   * @param DestService $service
   */
  public function __construct(DestService $service) {
    $this->service = $service;
  }

  /**
   * @param Archive\Events\ArchiveEvent $event
   *
   * @throws CopyToDestFailed
   */
  public function handle(Archive\Events\ArchiveEvent $event) {
    $this->service->copy($event->target);
  }
}
