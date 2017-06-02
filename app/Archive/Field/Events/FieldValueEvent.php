<?php

namespace Packages\Backup\App\Archive\Field\Events;

use App\Support\Event;
use App\Support\Database\SerializesModels;
use Packages\Backup\App\Archive\Field\Value;

abstract class FieldValueEvent extends Event
{
    use SerializesModels;

    /**
     * @var Value
     */
    public $target;

    /**
     * @param Value $target
     */
    public function __construct(Value $target)
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
