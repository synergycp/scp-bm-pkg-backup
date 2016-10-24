<?php

namespace Packages\Backup\App\Backup\Field;

use App\Database\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations;

class Value
extends Model
{
    public $table = 'pkg_backup_field_values';

    /**
     * @return string
     */
    public function value()
    {
        return $this->attributes['value'];
    }

    /**
     * @return Relations\BelongsTo
     */
    public function field()
    {
        return $this->belongsTo(
            Field::class
        );
    }

    /**
     * @return Relations\MorphTo
     */
    public function parent()
    {
        return $this->morphTo();
    }

    /**
     * @param Builder   $query
     * @param HasValues $parent
     *
     * @return Builder
     */
    public function scopeParent(Builder $query, HasValues $parent)
    {
        return $query
            ->where('parent_type', get_class($parent))
            ->where('parent_id', $parent->getKey())
            ;
    }
}
