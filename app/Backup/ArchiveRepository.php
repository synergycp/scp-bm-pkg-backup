<?php

namespace Packages\Backup\App\Backup;

use App\Database\ModelRepository;

class ArchiveRepository
extends ModelRepository
{
    protected $model = Archive::class;
}
