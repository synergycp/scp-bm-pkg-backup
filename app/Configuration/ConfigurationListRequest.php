<?php

namespace Packages\Backup\App\Configuration;

use App\Http\Requests\ListRequest;

class ConfigurationListRequest extends ListRequest {
  public $orders = [
    'created_at',
    'admin_id',
    'default' => ['created_at', 'desc'],
  ];
}
