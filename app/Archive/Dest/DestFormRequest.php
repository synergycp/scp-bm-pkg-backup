<?php

namespace Packages\Backup\App\Archive\Dest;

use Illuminate\Validation\Rule;
use App\Http\Requests\RestRequest;
use Packages\Backup\App\Archive\Handler\HandlerType;

class DestFormRequest extends RestRequest
{
    public function boot()
    {
        $this->rules = [
            'name'       => implode('|', ['required', $this->unique(Dest::class, 'name')]),
            'handler.id' => [
                'required',
                Rule::exists('pkg_backup_handlers', 'id')->where(function ($query) {
                    $query->where('type', HandlerType::DEST);
                }),
            ],
        ];
    }
}
