<?php

namespace Packages\Backup\App\Archive\Source\Events;

use App\Support\Event;
use App\Support\Database\SerializesModels;
use Packages\Backup\App\Archive\Source\Source;

abstract class SourceEvent extends Event
{
    use SerializesModels;

    /**
     * @var Source
     */
    public $target;

    /**
     * @param Source $target
     */
    public function __construct(Source $target)
    {
        $this->target = $target;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
