<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHandlersTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('pkg_backup_handlers', function (Blueprint $table) {
      $table->increments('id');

      $table->string('name');
      $table->unique('name');

      $table->text('class');

      $table->tinyInteger('type');
      $table->index('type');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::drop('pkg_backup_handlers');
  }
}
