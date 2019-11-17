<?php

use App\Support\Database\Blueprint;
use App\Support\Database\Migration;

class AddConfigurationBackupsTable extends Migration {
  /**
   * Run the migrations.
   */
  public function up() {
    $this->schema()->create('pkg_backup_configuration_backups', function (
      Blueprint $table
    ) {
      $table->increments('id');
      $table->foreign_id('admin')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down() {
    $this->schema()->drop('pkg_backup_configuration_backups');
  }
}
