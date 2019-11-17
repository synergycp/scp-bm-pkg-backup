<?php

namespace Packages\Backup\App\Archive\Dest;

use App\Auth\Permission\ICanHavePermissions;
use App\Auth\Permission\Rule\AllowIfUserHasPermissions;
use App\Auth\Permission\THasPermissionChecks;
use App\Database\Models\Model;
use Illuminate\Database\Eloquent\Collection;
use Packages\Backup\App\Archive;
use Illuminate\Database\Eloquent\Relations;

/**
 * Class Dest
 * @package Packages\Backup\App\Archive\Dest
 * @property Collection $fieldValues
 * @property Archive\Handler\Handler $handler
 */
class Dest extends Model implements
  Archive\Field\HasValues,
  ICanHavePermissions {
  use THasPermissionChecks;

  const PERMISSION_READ = Archive\Archive::PERMISSION_READ;
  const PERMISSION_WRITE = Archive\Archive::PERMISSION_WRITE;

  public $table = 'pkg_backup_destinations';

  public static $singular = 'Destination';
  public static $plural = 'Destinations';

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
