<?php

namespace Packages\Backup\App\Configuration;

use App\Admin\Admin;
use App\Auth\Permission\ICanHavePermissions;
use App\Auth\Permission\Rule\AllowIfUserHasPermissions;
use App\Auth\Permission\THasPermissionChecks;
use App\Database\Models\Model;
use Carbon\Carbon;
use Packages\Backup\App\Archive\Archive;

/**
 * Class Configuration
 * @package Packages\Backup\App\Configuration
 * @property Admin|null admin
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Configuration extends Model implements ICanHavePermissions {
  use THasPermissionChecks;

  const PERMISSION_READ = Archive::PERMISSION_READ;
  const PERMISSION_WRITE = Archive::PERMISSION_WRITE;

  public static $singular = 'Configuration Backup';
  public static $plural = 'Configuration Backups';

  public $table = 'pkg_backup_configuration_backups';

  /**
   * @inheritDoc
   */
  protected function permissionRulesForEdit(): array {
    return [AllowIfUserHasPermissions::create([self::PERMISSION_WRITE])];
  }

  public function admin() {
    return $this->hasOne(Admin::class, 'id', 'admin_id');
  }
}
