<?php

namespace Packages\Backup\App\Archive\Handler;

use App\Auth\Permission\THasPermissionChecks;
use App\Database\Models\Model;
use Illuminate\Database\Eloquent\Relations;
use Packages\Backup\App\Archive;

/**
 * Class Handler
 * @package Packages\Backup\App\Archive\Handler
 * @property string $class
 */
class Handler extends Model {
  use THasPermissionChecks;

  const PERMISSION_READ = Archive\Archive::PERMISSION_READ;

  const PERMISSION_WRITE = Archive\Archive::PERMISSION_WRITE;

  public $table = 'pkg_backup_handlers';

  protected $casts = ['type' => 'int'];

  /**
   * @return Relations\HasMany
   */
  public function fields() {
    return $this->hasMany(Archive\Field\Field::class);
  }

  /**
   * @inheritDoc
   */
  protected function permissionRulesForEdit(): array {
    return []; // No one can edit this except for migrations.
  }
}
