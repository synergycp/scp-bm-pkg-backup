<?php

namespace Packages\Backup\App\Backup\Recurring;

use App\Http\Requests\RestRequest;

class RecurringFormRequest extends RestRequest
{
    public function boot()
    {
        $this->rules = [
            'destination_id' => 'required|exists:pkg_backup_destinations,id',
            'source_id'      => 'required|exists:pkg_backup_sources,id',
            'period'         => 'required|integer',
        ];
    }
}