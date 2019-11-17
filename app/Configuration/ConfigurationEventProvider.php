<?php

namespace Packages\Backup\App\Configuration;

use App\Log\EventLogger;
use App\Support\EventServiceProvider;
use Packages\Backup\App\Configuration\Events\ConfigurationBackupCreated;

class ConfigurationEventProvider extends EventServiceProvider {
  /**
   * @var array
   */
  protected $listen = [
    ConfigurationBackupCreated::class => [EventLogger::class],
  ];
}
