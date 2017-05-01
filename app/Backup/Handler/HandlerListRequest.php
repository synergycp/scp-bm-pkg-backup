<?php

namespace Packages\Backup\App\Backup\Handler;

use App\Http\Requests\ListRequest;

class HandlerListRequest extends ListRequest
{
    public $orders = [];
}