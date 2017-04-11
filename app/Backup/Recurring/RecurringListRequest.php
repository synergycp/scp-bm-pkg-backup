<?php

namespace Packages\Backup\App\Backup\Recurring;

use App\Http\Requests\ListRequest;

class RecurringListRequest extends ListRequest
{
    public $orders = [];
}