<?php

namespace Packages\Backup\App\Archive\Events;

use Packages\Backup\App\Archive;

interface ArchiveStatusChangeEvent {
  /**
   * @return int
   */
  public function status();

  /**
   * @return Archive\Archive
   */
  public function target();
}
