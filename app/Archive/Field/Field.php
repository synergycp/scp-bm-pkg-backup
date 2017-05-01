<?php

namespace Packages\Backup\App\Archive\Field;

use App\Database\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations;
use Packages\Backup\App\Archive;

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
            Archive\Handler\Handler::class
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
