<?php

namespace Packages\Backup\App\Archive;

use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Contracts\Queue;

/**
 * Delete the oldest finished Archives on a Destination beyond its retention
 * limit. Unlike the per-Recurring limit, this caps the total number of
 * Archives on the Destination across all Recurring and manual Backups.
 */
class PruneDestinationArchives implements Queue\ShouldQueue {
  /**
   * @var EventDispatcher
   */
  protected $event;

  /**
   * @var ArchiveRepository
   */
  protected $archives;

  /**
   * @param EventDispatcher   $event
   * @param ArchiveRepository $archives
   */
  public function __construct(
    EventDispatcher $event,
    ArchiveRepository $archives
  ) {
    $this->event = $event;
    $this->archives = $archives;
  }

  /**
   * @param Events\ArchiveEvent $event
   */
  public function handle(Events\ArchiveEvent $event) {
    $dest = $event->target->dest;

    if (!$dest || !$dest->max_archives) {
      return;
    }

    $finished = $this->archives
      ->query()
      ->where('destination_id', $dest->getKey())
      ->where('status', ArchiveStatus::FINISHED)
      ->orderBy('created_at', 'desc')
      ->orderBy('id', 'desc')
      ->get();

    $finished
      ->slice((int) $dest->max_archives)
      ->each(function (Archive $stale) {
        $stale->delete();

        // The ArchiveDeleted listeners remove the remote file.
        $this->event->dispatch(new Events\ArchiveDeleted($stale));
      });
  }
}
