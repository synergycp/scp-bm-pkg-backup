<?php

namespace Packages\Backup\App\Backup\Recurring;

use App\Api\ApiAuthService;
use Illuminate\Support\Collection;
use App\Support\Http\UpdateService;

class RecurringUpdateService extends UpdateService
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
    protected $requestClass = RecurringFormRequest::class;

    public function boot(ApiAuthService $auth)
    {
        $this->auth = $auth;
    }

    public function fillData(Recurring $item)
    {
        $item->source_id      = $this->request->input('source_id');
        $item->destination_id = $this->request->input('destination_id');
        $item->period         = $this->request->input('period');
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