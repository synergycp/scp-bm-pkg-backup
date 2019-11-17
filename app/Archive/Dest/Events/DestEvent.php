<?php

namespace Packages\Backup\App\Archive\Dest\Events;

use App\Support\Event;
use App\Support\Database\SerializesModels;
use Packages\Backup\App\Archive\Dest\Dest;

abstract class DestEvent extends Event {
  use SerializesModels;

  /**
   * @var Dest
   */
  public $target;

  /**
   * @param Dest $target
   */
  public function __construct(Dest $target) {
    $this->target = $target;
  }

  /**
   * Get the channels the event should be broadcast on.
   *
   * @return array
   */
  public function broadcastOn() {
    return [];
  }
}
