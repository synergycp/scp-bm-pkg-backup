<?php

use Illuminate\Database\Migrations\Migration;
use Packages\Backup\App\Archive\Field;
use Packages\Backup\App\Archive\Source;
use Packages\Backup\App\Archive\Handler;
use Packages\Backup\App\Sources\Mysql;

class AddMysqlDump extends Migration {
  /**
   * Run the migrations.
   */
  public function up() {
    $handler = new Handler\Handler();
    $handler->name = 'MySQL Dump';
    $handler->type = Handler\HandlerType::SOURCE;
    $handler->class = Mysql\MysqlDumpHandler::class;
    $handler->save();

    $source = new Source\Source();
    $source->name = 'Main Database';
    $source->handler_id = $handler->id;
    $source->ext = 'gz';
    $source->save();

    $fieldNames = [Mysql\MysqlDumpFields::DATABASE];

    $fields = array_map(function ($name) use ($handler) {
      $field = new Field\Field();
      $field->name = $name;
      $handler->fields()->save($field);

      return $field;
    }, array_combine($fieldNames, $fieldNames));

    collection([
      Mysql\MysqlDumpFields::DATABASE => 'synergy',
    ])->each(function ($value, $name) use ($fields, $source) {
      $fieldValue = new Field\Value();
      $fieldValue->field_id = $fields[$name]->id;
      $fieldValue->value = $value;
      $fieldValue->parent()->associate($source);
      $fieldValue->save();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down() {
  }
}
