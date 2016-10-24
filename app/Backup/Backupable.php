<?php

namespace Packages\Backup\App\Backup;

use Illuminate\Database\Eloquent\Relations;

trait Backupable
{
    /**
     * @return Relations\BelongsTo
     */
    public function source()
    {
        return $this->belongsTo(
            Source\Source::class,
            'source_id'
        );
    }

    /**
     * @return Relations\BelongsTo
     */
    public function dest()
    {
        return $this->belongsTo(
            Dest\Dest::class,
            'destination_id'
        );
    }
}
