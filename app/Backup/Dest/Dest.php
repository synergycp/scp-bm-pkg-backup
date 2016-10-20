<?php

namespace Packages\Backup\App\Backup\Dest;

use App\Database\Models\Model;

class Dest
extends Model
{
    public $table = 'pkg_backup_destinations';
}
