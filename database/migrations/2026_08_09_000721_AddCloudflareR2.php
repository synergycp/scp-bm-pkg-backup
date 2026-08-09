<?php

use Illuminate\Database\Migrations\Migration;
use Packages\Backup\App\Archive\Field;
use Packages\Backup\App\Archive\Handler;
use Packages\Backup\App\Destinations\R2;

class AddCloudflareR2 extends Migration {
  /**
   * Run the migrations.
   */
  public function up() {
    $handler = new Handler\Handler();
    $handler->name = 'Cloudflare R2';
    $handler->type = Handler\HandlerType::DEST;
    $handler->class = R2\R2Handler::class;
    $handler->save();

    $secrets = [R2\R2Fields::ACCESS_KEY_ID, R2\R2Fields::SECRET_ACCESS_KEY];

    array_map(
      function ($name) use ($handler, $secrets) {
        $field = new Field\Field();
        $field->name = $name;
        $field->secret = in_array($name, $secrets);
        $handler->fields()->save($field);
      },
      [
        R2\R2Fields::ACCOUNT_ID,
        R2\R2Fields::ACCESS_KEY_ID,
        R2\R2Fields::SECRET_ACCESS_KEY,
        R2\R2Fields::BUCKET,
        R2\R2Fields::FOLDER,
      ]
    );
  }

  /**
   * Reverse the migrations.
   */
  public function down() {
    $handler = Handler\Handler::query()
      ->where('class', R2\R2Handler::class)
      ->first();

    if ($handler) {
      $handler->fields()->delete();
      $handler->delete();
    }
  }
}
