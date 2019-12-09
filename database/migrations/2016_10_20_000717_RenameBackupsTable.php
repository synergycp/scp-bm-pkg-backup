<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameBackupsTable extends Migration {
  /**
   * Run the migrations.
   */
  public function up() {
    Schema::rename('pkg_backup_backups', 'pkg_backup_archives');

    Schema::table('pkg_backup_archives', function (Blueprint $table) {
      $table
        ->integer('recurring_id')
        ->unsigned()
        ->nullable()
        ->change();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down() {
    Schema::table('pkg_backup_archives', function (Blueprint $table) {
      $table
        ->integer('recurring_id')
        ->unsigned()
        ->change();
    });

    Schema::rename('pkg_backup_archives', 'pkg_backup_backups');
  }
}
