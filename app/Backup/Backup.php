<?php

namespace Packages\Backup\App\Backup;

use App\Database\Models\Model;
use Illuminate\Database\Eloquent\Relations;

class Backup
extends Model
{
    public $table = 'pkg_backup_backups';

    /**
     * @return Relations\BelongsTo
     */
    public function target()
    {
        return $this->belongsTo(Source\Source::class, 'target_id');
    }
}
