<?php

namespace Packages\Backup\App\Archive\Source;

use Illuminate\Support\ServiceProvider;

class SourceServiceProvider extends ServiceProvider {
  /**
   * @var array
   */
  protected $providers = [SourceRoutesProvider::class];

  /**
   * Register the feature's service providers.
   */
  public function register() {
    $this->app->singleton(Handler\HandlerService::class);

    collection($this->providers)->each(_one([$this->app, 'register']));
  }

  /**
   * @return array
   */
  public function provides() {
    return [Handler\HandlerService::class];
  }
}
