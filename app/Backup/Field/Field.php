<?php

namespace Packages\Backup\App\Backup\Field;

use App\Database\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations;
use Packages\Backup\App\Backup;

class Field
extends Model
{
    public $table = 'pkg_backup_fields';

    /**
     * @return Relations\BelongsTo
     */
    public function handler()
    {
        return $this->belongsTo(
            Backup\Handler\Handler::class
        );
    }

    /**
     * @param Builder   $query
     * @param HasValues $handler
     *
     * @return Builder
     */
    public function scopeHandler(Builder $query, HasValues $handler)
    {
        return $query
            ->where('handler_id', $handler->getKey())
            ;
    }
}
