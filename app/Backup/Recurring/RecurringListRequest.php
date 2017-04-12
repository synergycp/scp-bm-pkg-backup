<?php

namespace Packages\Backup\App\Backup\Recurring;

use App\Http\Requests\ListRequest;

class RecurringListRequest extends ListRequest
{
    public $orders = [
        'source_id',
        'destination_id',
        'period',
        'status',
        'created_at',
        'updated_at',
    ];
}