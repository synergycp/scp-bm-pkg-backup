<?php

namespace Packages\Backup\App\Archive\Dest;

use App\Database\ModelRepository;

class DestinationRepository
extends ModelRepository
{
    protected $model = Dest::class;
}
