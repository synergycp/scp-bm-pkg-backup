<?php

namespace Packages\Backup\App\Backup\Recurring;

use App\Api\ApiAuthService;
use Illuminate\Support\Collection;
use App\Support\Http\UpdateService;
use Packages\Backup\App\Backup\Recurring\Events\RecurringCreated;

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

    /**
     * @var RecurringService
     */
    protected $recurringService;

    public function boot(ApiAuthService $auth, RecurringService $recurringService)
    {
        $this->auth             = $auth;
        $this->recurringService = $recurringService;
    }

    public function fillData(Recurring $item)
    {
        $item->source_id      = $this->request->input('source_id');
        $item->destination_id = $this->request->input('destination_id');
        $item->period         = $this->request->input('period');
    }

    public function afterCreate(Collection $items)
    {
        $createEvent = $this->queueHandler(RecurringCreated::class);

        $this->successItems('backup.recurring.created', $items->each($createEvent));

        $this->recurringService->createBackups($items);
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