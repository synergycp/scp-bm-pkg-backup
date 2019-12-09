<?php

namespace Packages\Backup\App\Archive\Source;

use App\Api\Transformer;

class SourceTransformer extends Transformer {
  /**
   * @param Source $item
   * @return mixed
   */
  public function item(Source $item) {
    return $item->expose(['id', 'name', 'ext']);
  }

  public function resource(Source $item) {
    return $this->item($item) + [];
  }
}
