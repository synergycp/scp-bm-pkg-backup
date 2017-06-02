<?php

namespace Packages\Backup\App\Archive\Dest;

use App\Http\Requests\RestRequest;

class DestFormRequest extends RestRequest
{
    public function boot()
    {
        $this->rules = [
            'name'       => 'required',
            'handler.id' => 'required|exists:pkg_backup_handlers,id',
        ];
    }
}
