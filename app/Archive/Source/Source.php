<?php

namespace Packages\Backup\App\Archive\Source;

use App\Auth\Permission\Rule\AllowIfUserHasPermissions;
use App\Auth\Permission\THasPermissionChecks;
use App\Database\Models\Model;
use Packages\Backup\App\Archive;
use Illuminate\Database\Eloquent\Relations;

/**
 * Class Source
 * @package Packages\Backup\App\Archive\Source
 * @property string name
 * @property string ext
 */
class Source extends Model implements Archive\Field\HasValues {
  use THasPermissionChecks;

  const PERMISSION_READ = Archive\Archive::PERMISSION_READ;
  const PERMISSION_WRITE = Archive\Archive::PERMISSION_WRITE;

  public $table = 'pkg_backup_sources';

  /**
   * @return Relations\MorphMany
   */
  public function fieldValues() {
    return $this->morphMany(Archive\Field\Value::class, 'parent');
  }

  /**
   * @return Relations\BelongsTo
   */
  public function handler() {
    return $this->belongsTo(Archive\Handler\Handler::class);
  }

  /**
   * @inheritDoc
   */
  protected function permissionRulesForEdit(): array {
    return [new AllowIfUserHasPermissions([self::PERMISSION_WRITE])];
  }
}
