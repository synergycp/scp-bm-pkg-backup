<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBackupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pkg_backup_backups', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('source_id')->unsigned();
            $table->foreign('source_id')
                ->references('id')
                ->on('pkg_backup_sources')
                ;

            $table->integer('destination_id')->unsigned();
            $table->foreign('destination_id')
                ->references('id')
                ->on('pkg_backup_destinations')
                ;

            $table->integer('recurring_id')->unsigned();
            $table->foreign('recurring_id')
                ->references('id')
                ->on('pkg_backup_recurring_backups')
                ;

            $table->tinyInteger('status');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pkg_backup_backups');
    }
}
