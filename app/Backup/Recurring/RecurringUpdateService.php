<?php

namespace Packages\Backup\App\Backup\Recurring;

use App\Api\ApiAuthService;
use Illuminate\Support\Collection;
use App\Support\Http\UpdateService;
use Packages\Backup\App\Backup\Recurring\Events\DestinationChanged;
use Packages\Backup\App\Backup\Recurring\Events\PeriodChanged;
use Packages\Backup\App\Backup\Recurring\Events\RecurringCreated;
use Packages\Backup\App\Backup\Recurring\Events\SourceChanged;

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

        $this->setSource($items);
        $this->setDestination($items);
        $this->setPeriod($items);
    }

    public function setSource(Collection $items)
    {
        $inputs = [
            'source_id' => $this->input('source_id'),
        ];

        $createEvent = $this->queueHandler(
            SourceChanged::class
        );

        $this->successItems(
            'backup.recurring.update.source',
            $items
                ->filter($this->changed($inputs))
                ->reject([$this, 'isCreating'])
                ->each($createEvent)
        );
    }

    public function setDestination(Collection $items)
    {
        $inputs = [
            'destination_id' => $this->input('destination_id'),
        ];

        $createEvent = $this->queueHandler(
            DestinationChanged::class
        );

        $this->successItems(
            'backup.recurring.update.destination',
            $items
                ->filter($this->changed($inputs))
                ->reject([$this, 'isCreating'])
                ->each($createEvent)
        );
    }

    public function setPeriod(Collection $items)
    {
        $inputs = [
            'period' => $this->input('period'),
        ];

        $createEvent = $this->queueHandler(
            PeriodChanged::class
        );

        $this->successItems(
            'backup.recurring.update.period',
            $items
                ->filter($this->changed($inputs))
                ->reject([$this, 'isCreating'])
                ->each($createEvent)
        );
    }
}