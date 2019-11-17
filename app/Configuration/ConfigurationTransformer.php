<?php

namespace Packages\Backup\App\Configuration;

use App\Api\Transformer;
use Illuminate\Database\Eloquent\Collection;

class ConfigurationTransformer extends Transformer {
  /**
   * @param Configuration $item
   * @return mixed
   */
  public function item(Configuration $item) {
    return $item->expose(['id']) + [
      'admin' => $item->admin ? $item->admin->expose(['id', 'name']) : null,
      'created_at' => $this->dateArr($item->created_at),
    ];
  }

  /**
   * @param Collection $items
   */
  public function itemPreload($items) {
    $items->load('admin');
  }

  public function resource(Configuration $item) {
    return $this->item($item) + [];
  }
}
