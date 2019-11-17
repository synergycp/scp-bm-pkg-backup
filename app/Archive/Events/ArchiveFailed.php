<?php

namespace Packages\Backup\App\Archive\Events;

use Packages\Backup\App\Archive;
use App\Log;

class ArchiveFailed extends ArchiveLoggableEvent implements
  Archive\Events\ArchiveStatusChangeEvent {
  use HasBackupStatus;

  /**
   * @var int
   */
  protected $status = Archive\ArchiveStatus::FAILED;

  /**
   * @var \Exception
   */
  protected $exc;

  /**
   * @param Archive\Archive $target
   * @param \Exception    $exc
   */
  public function __construct(Archive\Archive $target, \Exception $exc) {
    parent::__construct($target);

    $this->exc = $exc;
  }

  public function log(Log\Log $log) {
    $log
      ->setDesc('Backup failed')
      ->setTarget($this->target)
      ->setException($this->exc)
      ->save();
  }
}
