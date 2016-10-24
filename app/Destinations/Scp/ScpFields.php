<?php

namespace Packages\Backup\App\Destinations\Scp;

interface ScpFields
{
    /**
     * @var string
     */
    const HOST = 'host';

    /**
     * @var string
     */
    const USER = 'user';

    /**
     * @var string
     */
    const FOLDER = 'folder';
}
