<?php

namespace Packages\Backup\App\Archive;

use App\Database\Models\Model;
use Illuminate\Database\Eloquent\Relations;

class Archive
    extends Model
{
    use Archivable;

    public $table = 'pkg_backup_archives';
    public $attributes = [
        'status' => ArchiveStatus::QUEUED,
    ];

    protected $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime'];

    public static $controller = 'pkg.backup.archive';

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
