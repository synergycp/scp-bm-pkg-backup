<?php

namespace Packages\Backup\App\Recurring;

use Packages\Backup\App\Archive;
use App\Support\Collection;
use Illuminate\Database\Eloquent;

/**
 * Handle the Business Logic for Recurring Backups
 */
class RecurringService {
  /**
   * @var RecurringRepository
   */
  protected $recurrings;

  /**
   * @var Archive\ArchiveService
   */
  protected $backup;

  /**
   * @param RecurringRepository $recurrings
   * @param Archive\ArchiveService $backup
   */
  public function __construct(
    RecurringRepository $recurrings,
    Archive\ArchiveService $backup
  ) {
    $this->backup = $backup;
    $this->recurrings = $recurrings;
  }

  /**
   * Queue recurring backups.
   *
   * @return int
   */
  public function run() {
    $sum = 0;
    $createBackups = function (Eloquent\Collection $items) use (&$sum) {
      $sum += $items->count();
      $this->createBackups($items);
    };

    $this->recurrings
      ->query()
      ->ready()
      ->chunk(300, $createBackups);

    return $sum;
  }

  /**
   * @param Eloquent\Collection $items
   *
   * @return Collection
   */
  public function createBackups(Eloquent\Collection $items) {
    return $items->load('source', 'dest')->map([$this, 'createBackup']);
  }

  /**
   * @param Recurring $item
   *
   * @return Archive\Archive
   */
  public function createBackup(Recurring $item) {
    return $this->backup->create($item->source, $item->dest, $item);
  }
}
