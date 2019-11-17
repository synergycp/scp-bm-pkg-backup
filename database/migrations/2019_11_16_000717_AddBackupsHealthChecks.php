<?php

use App\Support\Database\Migration;
use Packages\Backup\App\Archive\ArchiveHealthCheckJob;
use Packages\Backup\App\Configuration\ConfigurationHealthCheckJob;
use Packages\Backup\App\Recurring\RecurringHealthCheckJob;

class AddBackupsHealthChecks extends Migration {
  /**
   * Run the migrations.
   */
  public function up() {
    dispatch(new ConfigurationHealthCheckJob());
    dispatch(new ArchiveHealthCheckJob());
    dispatch(new RecurringHealthCheckJob());
  }

  /**
   * Reverse the migrations.
   */
  public function down() {
    $this->deleteHealthCheck('pkg.backup.configuration.exists');
    $this->deleteHealthCheck('pkg.backup.archive.status');
    $this->deleteHealthCheck('pkg.backup.recurring.exists');
  }
}
