<?php

namespace Packages\Backup\App\Archive\Handler;

use Packages\Backup\App\Archive;
use App\Database\Models\Model;
use Illuminate\Database\Eloquent\Relations;

class Handler
extends Model
{
    public $table = 'pkg_backup_handlers';

    protected $casts = [
        'type' => 'int',
    ];

    /**
     * @return Relations\HasMany
     */
    public function fields()
    {
        return $this->hasMany(
            Archive\Field\Field::class
        );
    }
}
