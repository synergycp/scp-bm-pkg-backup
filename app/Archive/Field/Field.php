<?php

namespace Packages\Backup\App\Archive\Field;

use App\Auth\Permission\Rule\AllowIfUserHasPermissions;
use App\Auth\Permission\THasPermissionChecks;
use App\Database\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations;
use Packages\Backup\App\Archive;

class Field extends Model {
  use THasPermissionChecks;

  const PERMISSION_READ = Archive\Archive::PERMISSION_READ;

  const PERMISSION_WRITE = Archive\Archive::PERMISSION_WRITE;

  public $table = 'pkg_backup_fields';

  /**
   * @return Relations\BelongsTo
   */
  public function handler() {
    return $this->belongsTo(Archive\Handler\Handler::class);
  }

  /**
   * @param Builder   $query
   * @param HasValues $handler
   *
   * @return Builder
   */
  public function scopeHandler(Builder $query, HasValues $handler) {
    return $query->where('handler_id', $handler->getKey());
  }

  /**
   * @inheritDoc
   */
  protected function permissionRulesForEdit(): array {
    return [new AllowIfUserHasPermissions([self::PERMISSION_WRITE])];
  }
}
