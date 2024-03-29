<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFieldsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('pkg_backup_fields', function (Blueprint $table) {
      $table->increments('id');

      $table->string('name');

      $table->integer('handler_id')->unsigned();
      $table
        ->foreign('handler_id')
        ->references('id')
        ->on('pkg_backup_handlers');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::drop('pkg_backup_fields');
  }
}
