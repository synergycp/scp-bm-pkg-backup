<?php

namespace Packages\Backup\App\Backup\Source;

use App\Http\Requests\ListRequest;

class SourceListRequest extends ListRequest
{
    public $orders = [];
}