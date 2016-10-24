<?php

namespace Packages\Backup\App\Backup;

use App\Database\Models\Model;
use Illuminate\Database\Eloquent\Relations;

class Backup
extends Model
{
    use Backupable;

    public $table = 'pkg_backup_backups';
    public $attributes = [
        'status' => BackupStatus::QUEUED,
    ];

    /**
     * @return Relations\BelongsTo
     */
    public function recurring()
    {
        return $this->belongsTo(
            Recurring\Recurring::class,
            'recurring_id'
        );
    }
}
