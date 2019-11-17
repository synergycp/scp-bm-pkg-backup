<?php

namespace Packages\Backup\App\Archive;

use App\Auth\Permission\ICanHavePermissions;
use App\Auth\Permission\Rule\AllowIfUserHasPermissions;
use App\Auth\Permission\THasPermissionChecks;
use App\Database\Models\Model;
use Illuminate\Database\Eloquent\Relations;
use Packages\Backup\App\Archive\Dest\Dest;
use Packages\Backup\App\Recurring;

/**
 * Class Archive
 * @package Packages\Backup\App\Archive
 * @property int $period
 * @property int $recurring_id
 * @property int $status one of ArchiveStatus::*
 * @property Dest $dest
 */
class Archive extends Model implements ICanHavePermissions {
  use THasPermissionChecks;
  use Archivable;

  const PERMISSION_READ = 'pkg.backup.read';
  const PERMISSION_WRITE = 'pkg.backup.write';

  public $table = 'pkg_backup_archives';
  public $attributes = [
    'status' => ArchiveStatus::QUEUED,
  ];

  protected $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime'];

  public static $singular = 'Backup Archive';
  public static $plural = 'Backup Archives';

  public static $controller = 'pkg.backup.archive';

  public function getNameAttribute() {
    return $this->source->name;
  }

  /**
   * @return Relations\BelongsTo
   */
  public function recurring() {
    return $this->belongsTo(Recurring\Recurring::class, 'recurring_id');
  }

  /**
   * @inheritDoc
   */
  protected function permissionRulesForEdit(): array {
    return [new AllowIfUserHasPermissions([self::PERMISSION_WRITE])];
  }
}
