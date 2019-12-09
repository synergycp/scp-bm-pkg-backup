<?php

namespace Packages\Backup\App\Archive\Field;

use App\Http\Requests\ListRequest;

class FieldListRequest extends ListRequest {
  public $orders = [];
}
