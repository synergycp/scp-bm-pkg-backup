<?php

namespace Packages\Backup\App\Archive\Dest;

use App\Http\Requests\ListRequest;

class DestListRequest extends ListRequest
{
    public $orders = [];
}
