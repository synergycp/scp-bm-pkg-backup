<?php

namespace Packages\Backup\App\Archive\Dest;

use App\Database\Models\Model;
use Packages\Backup\App\Archive;

class Dest
extends Model
implements Archive\Field\HasValues
{
    public $table = 'pkg_backup_destinations';

    /**
     * @inheritDoc
     */
    public function fields()
    {
        return $this->morphMany(Archive\Field\Field::class, 'parent');
    }

    /**
     * @return Relations\BelongsTo
     */
    public function handler()
    {
        return $this->belongsTo(Archive\Handler\Handler::class);
    }
}
