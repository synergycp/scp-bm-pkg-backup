<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSourcesTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('pkg_backup_sources', function (Blueprint $table) {
      $table->increments('id');

      $table->string('name');
      $table->unique('name');
      $table->string('ext');

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
    Schema::drop('pkg_backup_sources');
  }
}
