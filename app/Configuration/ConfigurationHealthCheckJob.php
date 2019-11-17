<?php

namespace Packages\Backup\App\Configuration;

use App\Support\Job;
use App\System\Health\HealthStatusService;

class ConfigurationHealthCheckJob extends Job {
  const HEALTH_SLUG_HAS_BACKUP = 'pkg.backup.configuration.exists';

  public function handle(
    ConfigurationRepository $backups,
    HealthStatusService $status
  ) {
    $backups->query()->exists()
      ? $status->ok(static::HEALTH_SLUG_HAS_BACKUP)
      : $status->warn(static::HEALTH_SLUG_HAS_BACKUP);
  }
}
