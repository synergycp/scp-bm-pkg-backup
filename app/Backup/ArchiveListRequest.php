<?php

namespace Packages\Backup\App\Backup;

use App\Http\Requests\ListRequest;

class ArchiveListRequest extends ListRequest
{
    public $orders = [
        'source_id',
        'destination_id',
        'recurring_id',
        'status',
        'created_at',
        'updated_at',
    ];
}