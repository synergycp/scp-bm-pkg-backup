<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBackupRetention extends Migration {
  /**
   * Run the migrations.
   */
  public function up() {
    Schema::table('pkg_backup_recurring_backups', function (Blueprint $table) {
      // Number of finished Archives to keep per Recurring Backup.
      // Null keeps everything.
      $table
        ->integer('max_archives')
        ->unsigned()
        ->nullable();
    });

    Schema::table('pkg_backup_destinations', function (Blueprint $table) {
      // Maximum number of finished Archives to keep on this Destination,
      // across all Recurring and manual Backups. Null keeps everything.
      $table
        ->integer('max_archives')
        ->unsigned()
        ->nullable();
    });

    Schema::table('pkg_backup_archives', function (Blueprint $table) {
      // Remote file name, recorded when the Archive is copied to its
      // Destination.
      $table->string('dest_file')->nullable();

      // Speeds up the "latest Archive per Recurring Backup" lookup that
      // decides which Recurring Backups are due.
      $table->index(
        ['recurring_id', 'created_at'],
        'pkg_backup_archives_recurring_created_idx'
      );
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down() {
    Schema::table('pkg_backup_archives', function (Blueprint $table) {
      $table->dropIndex('pkg_backup_archives_recurring_created_idx');
      $table->dropColumn('dest_file');
    });

    Schema::table('pkg_backup_recurring_backups', function (Blueprint $table) {
      $table->dropColumn('max_archives');
    });

    Schema::table('pkg_backup_destinations', function (Blueprint $table) {
      $table->dropColumn('max_archives');
    });
  }
}
