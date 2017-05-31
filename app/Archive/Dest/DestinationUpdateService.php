<?php

namespace Packages\Backup\App\Archive\Dest;

use Illuminate\Support\Collection;
use App\Support\Http\UpdateService;
use Packages\Backup\App\Archive\Field\Value;

class DestinationUpdateService extends UpdateService
{
    /**
     * @var ServerFormRequest
     */
    protected $request;
    protected $requestClass = DestinationFormRequest::class;

    public function afterCreate(Collection $items)
    {
        $this->createFields($items);

        $createEvent = $this->queueHandler(Events\DestinationCreated::class);

        $this->successItems('pkg.backup::destination.created', $items->each($createEvent));
    }

    protected function updateAll(Collection $items)
    {
        $this->setName($items);
        $this->setHandler($items);
        $this->setFields($items);
    }

    /**
     * @param Collection $items
     */
    private function setName(Collection $items)
    {
        $changed = $this->changed([
            'name' => $this->input('name'),
        ]);

        $event = $this->queueHandler(
            Events\NameChanged::class
        );

        $this->successItems(
            'pkg.backup::destination.update.name',
            $items
                ->filter($changed)
                ->reject([$this, 'isCreating'])
                ->each($event)
        );
    }

    private function setHandler(Collection $items)
    {
        $changed = $this->changed([
            'handler_id' => $this->input('handler.id'),
        ]);

        $event = $this->queueHandler(
            Events\HandlerChanged::class
        );

        $this->successItems(
            'pkg.backup::destination.update.handler',
            $items
                ->filter($changed)
                ->reject([$this, 'isCreating'])
                ->each($event)
        );
    }

    private function setFields(Collection $items)
    {
        if (!$this->create) {
            // TODO update fields values
        }
    }

    private function createFields(Collection $items)
    {
        $items->each(function ($item) {
            $fields = collect($this->input('fields'))
                ->map(function ($value, $id) use ($item) {
                    $entity = new Value();

                    $entity->field_id = $id;
                    $entity->value    = $value;

                    return $entity;
                });

            $item->values()->saveMany($fields);
        });
    }

}
