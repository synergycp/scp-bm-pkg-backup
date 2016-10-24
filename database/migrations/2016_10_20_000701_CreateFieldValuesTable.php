<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFieldValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pkg_backup_field_values', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('field_id')
                ->unsigned();
            $table->foreign('field_id')
                ->references('id')
                ->on('pkg_backup_fields')
                ;

            $table->morphs('parent');

            $table->text('value');

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
        Schema::drop('pkg_backup_field_values');
    }
}
