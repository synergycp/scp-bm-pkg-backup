<?php

namespace Packages\Backup\App\Configuration;

use App\Support\ClassMap;

class ConfigurationServiceProvider extends \App\Support\ServiceProvider {
  /**
   * @var array
   */
  protected $providers = [
    ConfigurationEventProvider::class,
    ConfigurationRoutesProvider::class,
  ];

  /**
   * Boot the Backup Service Feature.
   *
   * @param ClassMap $classMap
   */
  public function boot(ClassMap $classMap) {
    $classMap->map('pkg.backup.configuration', Configuration::class);
  }
}
