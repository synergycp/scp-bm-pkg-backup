<?php

namespace Packages\Backup\App\Archive\Dest;

use App\Api\Transformer;
use Packages\Backup\App\Archive\Field\Value;

class DestTransformer extends Transformer {
  /**
   * @param Dest $item
   * @return mixed
   */
  public function item(Dest $item) {
    return $item->expose(['id', 'name']) + $this->handler($item);
  }

  private function handler(Dest $item) {
    return [
      'handler' => $item->handler->expose(['name']) + [
        'fields' => $this->fields($item),
      ],
    ];
  }

  private function fields(Dest $item) {
    $fields = [];

    $item->fieldValues->each(function (Value $field) use (&$fields) {
      $fields[$field->field_id] = [
        'id' => $field->field_id,
        'name' => $field->field->name,
        'value' => $field->value,
      ];
    });

    return $fields;
  }

  public function resource(Dest $item) {
    return $this->item($item) + [];
  }
}
