<?php

namespace Packages\Backup\App\Archive\Field;

use App\Api\Transformer;

class FieldTransformer extends Transformer {
  /**
   * @param Field $item
   * @return mixed
   */
  public function item(Field $item) {
    return $item->expose(['id', 'name']) + [
      'handler' => $item->handler->expose(['name']),
    ];
  }

  public function resource(Field $item) {
    return $this->item($item) + [];
  }
}
