<?php

namespace Packages\Backup\App\Recurring;

use App\Support\Job;
use App\System\Health\HealthStatusService;

class RecurringHealthCheckJob extends Job {
  const HEALTH_SLUG_BACKUP_EXISTS = 'pkg.backup.recurring.exists';

  public function handle(
    RecurringRepository $recurrings,
    HealthStatusService $status
  ) {
    $recurrings->query()->exists()
      ? $status->ok(static::HEALTH_SLUG_BACKUP_EXISTS)
      : $status->warn(static::HEALTH_SLUG_BACKUP_EXISTS);
  }
}
