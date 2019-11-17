<?php

namespace Packages\Backup\App\Configuration;

use App\Log\EventLogger;
use App\Support\EventServiceProvider;
use App\Support\Jobs\JobRunnerEventListener;
use Packages\Backup\App\Configuration\Events\ConfigurationBackupCreated;

class ConfigurationEventProvider extends EventServiceProvider {
  /**
   * @var array
   */
  protected $listen = [
    ConfigurationBackupCreated::class => [
      EventLogger::class,
      JobRunnerEventListener::class,
    ],
  ];
}
