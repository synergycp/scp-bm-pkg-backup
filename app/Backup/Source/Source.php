<?php

namespace Packages\Backup\App\Backup\Source;

use App\Database\Models\Model;
use Packages\Backup\App\Backup;
use Illuminate\Database\Eloquent\Relations;

class Source
extends Model
implements Backup\Field\HasValues
{
    public $table = 'pkg_backup_sources';

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
