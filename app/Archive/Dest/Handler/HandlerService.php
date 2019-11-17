<?php

namespace Packages\Backup\App\Archive\Dest\Handler;

use Illuminate\Foundation\Application;
use Packages\Backup\App\Archive;

/**
 * Handle the Business Logic for Backup Handlers.
 */
class HandlerService {
  /**
   * @var Application
   */
  protected $app;

  /**
   * @param Application $app
   */
  public function __construct(Application $app) {
    $this->app = $app;
  }

  /**
   * @param Archive\Dest\Dest $dest
   *
   * @return Handler
   */
  public function get(Archive\Dest\Dest $dest) {
    return $this->make($dest->handler);
  }

  /**
   * @param Archive\Handler\Handler $handler
   *
   * @return Handler
   */
  public function make(Archive\Handler\Handler $handler) {
    return $this->app->make($handler->class);
  }
}
