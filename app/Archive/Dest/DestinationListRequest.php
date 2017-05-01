<?php

namespace Packages\Backup\App\Archive\Dest;

use App\Http\Requests\ListRequest;

class DestinationListRequest extends ListRequest
{
    public $orders = [];
}
