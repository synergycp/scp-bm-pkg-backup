<?php

namespace Packages\Backup\App\Archive;

use App\DatetimeService;
use App\Support\Job;
use App\System\Health\HealthStatusService;

class ArchiveHealthCheckJob extends Job {
  const HEALTH_SLUG_BACKUP_STATUS = 'pkg.backup.archive.status';

  /**
   * Archives still in progress after this many hours are considered stuck
   * (e.g. their queue worker died) and are marked as failed.
   *
   * @var int
   */
  const STUCK_AFTER_HOURS = 24;

  public function handle(
    ArchiveRepository $archives,
    ArchiveService $service,
    DatetimeService $dateTime,
    HealthStatusService $status
  ) {
    $this->failStuckArchives($archives, $service, $dateTime);

    $latest = $this->latestTerminalPerDestination($archives);

    if ($latest->isEmpty()) {
      $status->warn(static::HEALTH_SLUG_BACKUP_STATUS);

      return;
    }

    $anyFailed = $latest->contains(function (Archive $archive) {
      return (int) $archive->status === ArchiveStatus::FAILED;
    });

    $anyFailed
      ? $status->error(self::HEALTH_SLUG_BACKUP_STATUS)
      : $status->ok(self::HEALTH_SLUG_BACKUP_STATUS);
  }

  /**
   * Mark Archives that have been stuck in a non-terminal status for too long
   * as failed, so they are visible and do not linger forever.
   *
   * @param ArchiveRepository $archives
   * @param ArchiveService    $service
   * @param DatetimeService   $dateTime
   */
  protected function failStuckArchives(
    ArchiveRepository $archives,
    ArchiveService $service,
    DatetimeService $dateTime
  ) {
    $cutoff = $dateTime
      ->now()
      ->subHours(self::STUCK_AFTER_HOURS)
      ->format($dateTime->databaseFormat());

    $archives
      ->query()
      ->whereIn('status', [
        ArchiveStatus::QUEUED,
        ArchiveStatus::COMPRESS,
        ArchiveStatus::COPYING,
      ])
      ->where('updated_at', '<=', $cutoff)
      ->get()
      ->each(function (Archive $backup) use ($service) {
        $service->failed(
          $backup,
          new \Exception(sprintf(
            'Backup stuck in progress for over %d hours.',
            self::STUCK_AFTER_HOURS
          ))
        );
      });
  }

  /**
   * The most recent finished or failed Archive for each Destination, so a
   * failing Destination is not masked by a healthy one backing up later.
   *
   * @param ArchiveRepository $archives
   *
   * @return \Illuminate\Database\Eloquent\Collection
   */
  protected function latestTerminalPerDestination(ArchiveRepository $archives) {
    $latestIds = $archives
      ->query()
      ->whereIn('status', [ArchiveStatus::FINISHED, ArchiveStatus::FAILED])
      ->groupBy('destination_id')
      ->selectRaw('MAX(id) as id')
      ->pluck('id');

    return $archives
      ->query()
      ->whereIn('id', $latestIds)
      ->get();
  }
}
