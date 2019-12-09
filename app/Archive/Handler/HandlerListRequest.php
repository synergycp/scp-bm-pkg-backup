<?php

namespace Packages\Backup\App\Archive\Handler;

use App\Http\Requests\ListRequest;

class HandlerListRequest extends ListRequest {
  public $orders = [];
}
