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
     * @var ArchiveFormRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $requestClass = ArchiveFormRequest::class;

    public function boot(ApiAuthService $auth)
    {
        $this->auth = $auth;
    }

    public function fillData(Archive $item)
    {
        $item->source_id      = $this->request->input('source_id');
        $item->destination_id = $this->request->input('destination_id');
    }

    public function afterCreate(Collection $items)
    {
        $createEvent = $this->queueHandler(Events\BackupCreated::class);

        $this->successItems('backup.created', $items->each($createEvent));
    }

    /**
     * Update all archives using the given request.
     *
     * @param Collection $items
     */
    public function updateAll(Collection $items)
    {
        if ($this->create) {
            return $items->map([$this, 'fillData']);
        }
    }
}