<?php

namespace Packages\Backup\App\Backup\Dest;

use App\Database\Models\Model;
use Packages\Backup\App\Backup;

class Dest
extends Model
implements Backup\Field\HasValues
{
    public $table = 'pkg_backup_destinations';

    /**
     * @inheritDoc
     */
    public function fields()
    {
        return $this->morphMany(Backup\Field\Field::class, 'parent');
    }

    /**
     * @return Relations\BelongsTo
     */
    public function handler()
    {
        return $this->belongsTo(Backup\Handler\Handler::class);
    }
}
