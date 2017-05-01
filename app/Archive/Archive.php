<?php

namespace Packages\Backup\App\Archive;

use App\Database\Models\Model;
use Illuminate\Database\Eloquent\Relations;
use Packages\Backup\App\Recurring;

class Archive
    extends Model
{
    use Archivable;

    public $table = 'pkg_backup_archives';
    public $attributes = [
        'status' => ArchiveStatus::QUEUED,
    ];

    protected $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime'];

    public static $singular = 'Archive';
    public static $plural = 'Archives';

    public static $controller = 'pkg.backup.archive';

    public function getNameAttribute()
    {
        return $this->source->name;
    }

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
