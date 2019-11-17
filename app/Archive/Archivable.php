<?php

namespace Packages\Backup\App\Archive;

use Illuminate\Database\Eloquent\Relations;

/**
 * Trait Archivable
 * @package Packages\Backup\App\Archive
 * @property Source\Source $source
 * @property int $source_id
 * @property int $destination_id
 * @property int $period
 */
trait Archivable {
  /**
   * @return Relations\BelongsTo
   */
  public function source() {
    return $this->belongsTo(Source\Source::class, 'source_id');
  }

  /**
   * @return Relations\BelongsTo
   */
  public function dest() {
    return $this->belongsTo(Dest\Dest::class, 'destination_id');
  }
}
