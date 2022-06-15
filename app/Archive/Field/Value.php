<?php

namespace Packages\Backup\App\Archive\Field;

use App\Auth\Permission\ICanHavePermissions;
use App\Auth\Permission\Rule\AllowIfUserHasPermissions;
use App\Auth\Permission\THasPermissionChecks;
use App\Database\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations;
use Packages\Backup\App\Archive\Archive;

/**
 * Class Value
 * @package Packages\Backup\App\Archive\Field
 * @property int $field_id
 * @property string $value
 * @property Field $field
 */
class Value extends Model implements ICanHavePermissions {
  use THasPermissionChecks;

  const PERMISSION_READ = Archive::PERMISSION_READ;
  const PERMISSION_WRITE = Archive::PERMISSION_WRITE;

  public $table = 'pkg_backup_field_values';

  /**
   * @return string
   */
  public function value() {
    return $this->attributes['value'];
  }

  /**
   * @return Relations\BelongsTo
   */
  public function field() {
    return $this->belongsTo(Field::class);
  }

  /**
   * @return Relations\MorphTo
   */
  public function parent() {
    return $this->morphTo();
  }

  /**
   * @param Builder   $query
   * @param HasValues $parent
   *
   * @return Builder
   */
  public function scopeParent(Builder $query, HasValues $parent) {
    return $query
      ->where('parent_type', get_class($parent))
      ->where('parent_id', $parent->getKey());
  }

  /**
   * @inheritDoc
   */
  protected function permissionRulesForEdit(): array {
    return [AllowIfUserHasPermissions::create([self::PERMISSION_WRITE])];
  }
}
