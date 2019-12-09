<?php

namespace Packages\Backup\App\Archive\Source;

use App\Http\Requests\ListRequest;

class SourceListRequest extends ListRequest {
  public $orders = [];
}
