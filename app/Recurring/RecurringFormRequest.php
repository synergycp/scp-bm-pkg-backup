<?php

namespace Packages\Backup\App\Recurring;

use App\Http\Requests\RestRequest;

class RecurringFormRequest extends RestRequest
{
    public function boot()
    {
        $this->rules = [
            'dest.id'   => 'required|exists:pkg_backup_destinations,id',
            'source.id' => 'required|exists:pkg_backup_sources,id',
            'period'    => 'required|integer|min:0',
        ];
    }
}
