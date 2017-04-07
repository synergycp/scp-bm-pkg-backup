<?php

namespace Packages\Backup\App\Backup;

use App\Database\ModelRepository;

class ArchiveRepository
extends ModelRepository
{
    protected $model = Archive::class;

    /**
     * @param Archive $item
     */
//    public function boot(
//        Archive $item,
//        ArchiveFilterService $filterService
//    ) {
//        $this->setItem($item);
//    }

//    public function request(ArchiveListRequest $request)
//    {
//        $query = $this->query();
//        return $this->filter->request($request, $query);
//    }
}
