<?php

use Illuminate\Database\Migrations\Migration;
use Packages\Backup\App\Archive\Dest;
use Packages\Backup\App\Archive\Field;
use Packages\Backup\App\Archive\Handler;
use Packages\Backup\App\Destinations\Scp;

class AddScp extends Migration {
  /**
   * Run the migrations.
   */
  public function up() {
    $handler = new Handler\Handler();
    $handler->name = 'Secure Copy via SSH';
    $handler->type = Handler\HandlerType::DEST;
    $handler->class = Scp\ScpHandler::class;
    $handler->save();

    array_map(
      function ($name) use ($handler) {
        $field = new Field\Field();
        $field->name = $name;
        $handler->fields()->save($field);
      },
      [Scp\ScpFields::HOST, Scp\ScpFields::USER, Scp\ScpFields::FOLDER]
    );
  }

  /**
   * Reverse the migrations.
   */
  public function down() {
  }
}
