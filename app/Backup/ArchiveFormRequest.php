<?php

namespace Packages\Backup\App\Backup;

use App\Http\Requests\RestRequest;

class ArchiveFormRequest extends RestRequest
{
    public function boot()
    {
        $this->rules = [];
    }
}