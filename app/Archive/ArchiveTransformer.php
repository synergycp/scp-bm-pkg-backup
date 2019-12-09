<?php

namespace Packages\Backup\App\Archive;

use App\Api\Transformer;

class ArchiveTransformer extends Transformer {
  private $statuses = [
    ArchiveStatus::QUEUED => 'Queued',
    ArchiveStatus::COMPRESS => 'Compressing',
    ArchiveStatus::COPYING => 'Copying Off-site',
    ArchiveStatus::FINISHED => 'Finished',
    ArchiveStatus::FAILED => 'Failed',
  ];

  public function itemPreload($items) {
    $items->load('source', 'dest');
  }

  public function resource(Archive $item) {
    return $this->item($item) + [];
  }

  /**
   * @param Archive $item
   *
   * @return mixed
   */
  public function item(Archive $item) {
    return $item->expose(['id', 'dest', 'source']) + [
      'name' => $item->source->name,
      'created_at' => $this->dateArr($item->created_at),
      'updated_at' => $this->dateArr($item->updated_at),
      'status' => $this->itemStatus($item),
    ];
  }

  private function itemStatus(Archive $item) {
    return $this->statuses[$item->status];
  }
}
