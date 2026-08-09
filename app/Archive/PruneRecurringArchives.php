<?php

namespace Packages\Backup\App\Archive;

use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Contracts\Queue;

/**
 * Delete the oldest finished Archives of a Recurring Backup beyond its
 * retention limit, so destinations do not fill up indefinitely.
 */
class PruneRecurringArchives implements Queue\ShouldQueue {
  /**
   * @var EventDispatcher
   */
  protected $event;

  /**
   * @param EventDispatcher $event
   */
  public function __construct(EventDispatcher $event) {
    $this->event = $event;
  }

  /**
   * @param Events\ArchiveEvent $event
   */
  public function handle(Events\ArchiveEvent $event) {
    $recurring = $event->target->recurring;

    if (!$recurring || !$recurring->max_archives) {
      return;
    }

    $finished = $recurring
      ->archives()
      ->where('status', ArchiveStatus::FINISHED)
      ->orderBy('created_at', 'desc')
      ->orderBy('id', 'desc')
      ->get();

    $finished
      ->slice((int) $recurring->max_archives)
      ->each(function (Archive $stale) {
        $stale->delete();

        // The ArchiveDeleted listeners remove the remote file.
        $this->event->dispatch(new Events\ArchiveDeleted($stale));
      });
  }
}
