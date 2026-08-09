<?php

namespace Packages\Backup\App\Archive\Dest;

use Illuminate\Contracts\Queue;
use Packages\Backup\App\Archive;

/**
 * Remove the remote copy of a Backup when its Archive record is deleted.
 */
class DeleteArchiveFromDest implements Queue\ShouldQueue {
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
   */
  public function handle(Archive\Events\ArchiveEvent $event) {
    $backup = $event->target;

    // Nothing was copied to the destination yet for these statuses.
    $notCopied = [Archive\ArchiveStatus::QUEUED, Archive\ArchiveStatus::COMPRESS];
    if (in_array((int) $backup->status, $notCopied)) {
      return;
    }

    try {
      $this->service->deleteFromDest($backup);
    } catch (\Exception $exc) {
      // The Archive record is already gone, so the remote file (if it exists)
      // is orphaned. Log it instead of failing the deletion.
      \Log::warning(sprintf(
        'Unable to delete Backup Archive %d from its destination: %s',
        $backup->getKey(),
        $exc->getMessage()
      ));
    }
  }
}
