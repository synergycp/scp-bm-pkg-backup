<?php

namespace Packages\Backup\App\Backup\Dest;

class CopyToDestFailed
extends \Exception
{
    /**
     * @param \Exception $previous
     */
    public function __construct(\Exception $previous)
    {
        parent::__construct(sprintf(
            'Copy Backup to Destination Failed: %s',
            $previous->getMessage()
        ), null, $previous);
    }
}
