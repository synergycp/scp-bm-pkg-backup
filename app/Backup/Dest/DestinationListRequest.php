<?php

namespace Packages\Backup\App\Backup\Dest;

use App\Http\Requests\ListRequest;

class DestinationListRequest extends ListRequest
{
    public $orders = [];
}