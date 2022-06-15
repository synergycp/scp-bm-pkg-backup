<?php

namespace Packages\Backup\App\Recurring;

use App\Auth\Permission\ICanHavePermissions;
use App\Auth\Permission\Rule\AllowIfUserHasPermissions;
use App\Auth\Permission\THasPermissionChecks;
use App\Database\Models\Model;
use Illuminate\Database\Eloquent;
use Packages\Backup\App\Archive;

/**
 * Class Recurring
 * @package Packages\Backup\App\Recurring
 * @property int $period
 */
class Recurring extends Model implements ICanHavePermissions {
  use Archive\Archivable;
  use THasPermissionChecks;

  const PERMISSION_READ = Archive\Archive::PERMISSION_READ;
  const PERMISSION_WRITE = Archive\Archive::PERMISSION_WRITE;

  public static $singular = 'Recurring Backup';
  public static $plural = 'Recurring Backups';
  public static $controller = 'pkg.backup.recurring';

  public $table = 'pkg_backup_recurring_backups';

  public function getNameAttribute() {
    return $this->source->name;
  }

  /**
   * @param Eloquent\Builder $query
   *
   * @return Eloquent\Builder
   */
  public function scopeReady(Eloquent\Builder $query) {
    $dateTime = app(\App\DatetimeService::class);
    $date = $dateTime->now()->format($dateTime->databaseFormat());

    return $this->scopeJoinLatest($query)
      ->addSelect('pkg_backup_recurring_backups.*')
      ->groupBy('pkg_backup_recurring_backups.id')
      ->havingRaw('latest_date IS NULL')
      ->orHaving(
        \DB::raw('latest_date'),
        '<=',
        \DB::raw("'$date' - INTERVAL $this->table.period SECOND")
      );
  }

  /**
   * @param Eloquent\Builder $query
   * @param string           $joinType
   * @param string           $alias
   * @param string           $date
   *
   * @return Eloquent\Builder
   */
  public function scopeJoinLatest(
    Eloquent\Builder $query,
    $joinType = 'left',
    $alias = 'latest',
    $date = 'latest_date'
  ) {
    return $query
      ->addSelect(\DB::raw("MAX($alias.created_at) as $date"))
      ->join(
        "pkg_backup_archives as $alias",
        "$alias.recurring_id",
        '=',
        "$this->table.id",
        $joinType
      );
  }

  public function archives() {
    return $this->hasMany(Archive\Archive::class);
  }

  /**
   * @inheritDoc
   */
  protected function permissionRulesForEdit(): array {
    return [AllowIfUserHasPermissions::create([self::PERMISSION_WRITE])];
  }
}
