<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Packages\Backup\App\Archive\Field;
use Packages\Backup\App\Archive\Handler;
use Packages\Backup\App\Destinations\B2;

class AddBackblazeB2 extends Migration {
  /**
   * Run the migrations.
   */
  public function up() {
    Schema::table('pkg_backup_fields', function (Blueprint $table) {
      // Values of secret fields are encrypted at rest.
      $table
        ->boolean('secret')
        ->default(false);
    });

    $handler = new Handler\Handler();
    $handler->name = 'Backblaze B2';
    $handler->type = Handler\HandlerType::DEST;
    $handler->class = B2\B2Handler::class;
    $handler->save();

    $secrets = [B2\B2Fields::KEY_ID, B2\B2Fields::APPLICATION_KEY];

    array_map(
      function ($name) use ($handler, $secrets) {
        $field = new Field\Field();
        $field->name = $name;
        $field->secret = in_array($name, $secrets);
        $handler->fields()->save($field);
      },
      [
        B2\B2Fields::KEY_ID,
        B2\B2Fields::APPLICATION_KEY,
        B2\B2Fields::BUCKET,
        B2\B2Fields::FOLDER,
      ]
    );
  }

  /**
   * Reverse the migrations.
   */
  public function down() {
    $handler = Handler\Handler::query()
      ->where('class', B2\B2Handler::class)
      ->first();

    if ($handler) {
      $handler->fields()->delete();
      $handler->delete();
    }

    Schema::table('pkg_backup_fields', function (Blueprint $table) {
      $table->dropColumn('secret');
    });
  }
}
