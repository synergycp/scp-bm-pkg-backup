<?php

namespace Packages\Backup\App\Backup;

use App\Http\Requests\RestRequest;

class ArchiveFormRequest extends RestRequest
{
    public function boot()
    {
        $this->rules = [
            'destination.id' => 'required|exists:pkg_backup_destinations,id',
            'source.id'      => 'required|exists:pkg_backup_sources,id',
            'recurring.id'   => 'sometimes|exists:pkg_backup_recurring_backups,id',
        ];
    }
}