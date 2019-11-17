<?php

namespace Packages\Backup\App\Archive;

use App\Support\Job;
use App\System\Health\HealthStatusService;

class ArchiveHealthCheckJob extends Job {
  const HEALTH_SLUG_BACKUP_STATUS = 'pkg.backup.archive.status';

  public function handle(
    ArchiveRepository $archives,
    HealthStatusService $status
  ) {
    $latestTwo = $archives
      ->query()
      ->orderBy('created_at', 'desc')
      ->take(2)
      ->get();
    $latest = array_get($latestTwo, 0);
    $previous = array_get($latestTwo, 1);

    if (!$latest) {
      $status->warn(static::HEALTH_SLUG_BACKUP_STATUS);

      return;
    }

    switch ($latest->status) {
      case ArchiveStatus::FAILED:
        $status->error(self::HEALTH_SLUG_BACKUP_STATUS);

        return;
      case ArchiveStatus::FINISHED:
        $status->ok(self::HEALTH_SLUG_BACKUP_STATUS);

        return;
    }

    $previous && $previous->status === ArchiveStatus::FINISHED
      ? $status->ok(self::HEALTH_SLUG_BACKUP_STATUS)
      : $status->warn(self::HEALTH_SLUG_BACKUP_STATUS);
  }
}
