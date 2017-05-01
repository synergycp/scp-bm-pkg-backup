<?php

namespace Packages\Backup\App\Archive\Dest;

class DeleteFromDestFailed
extends \Exception
{
    /**
     * @param \Exception $previous
     */
    public function __construct(\Exception $previous)
    {
        parent::__construct(sprintf(
            'Delete Backup from Destination Failed: %s',
            $previous->getMessage()
        ));
    }
}
