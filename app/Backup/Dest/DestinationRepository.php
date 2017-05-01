<?php

namespace Packages\Backup\App\Backup\Dest;

use App\Database\ModelRepository;

class DestinationRepository
extends ModelRepository
{
    protected $model = Dest::class;
}
