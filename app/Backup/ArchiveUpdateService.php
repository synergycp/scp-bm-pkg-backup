<?php

namespace Packages\Backup\App\Backup;

use App\Api\ApiAuthService;
use Illuminate\Support\Collection;
use App\Support\Http\UpdateService;

class ArchiveUpdateService extends UpdateService
{
    /**
     * @var ApiAuthService
     */
    protected $auth;

    /**
     * @var ArchiveListRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $requestClass = ArchiveListRequest::class;

    public function boot(ApiAuthService $auth)
    {
        $this->auth = $auth;
    }

    public function afterCreate(Collection $items)
    {
        $createEvent = $this->queueHandler(Events\BackupCreated::class);
        
        $this->successItems('backup.created', $items->each($createEvent));
    }

    /**
     * Update all Abuse Reports using the given request.
     *
     * @param Collection $items
     */
    public function updateAll(Collection $items)
    {

    }
}