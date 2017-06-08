<?php

namespace Packages\Backup\App\Archive\Dest;

use Illuminate\Support\Collection;
use App\Support\Http\UpdateService;
use Packages\Backup\App\Archive\Field\Value;
use Packages\Backup\App\Archive\Field\Events\FieldValueChanged;

class DestUpdateService extends UpdateService
{
    /**
     * @var ServerFormRequest
     */
    protected $request;
    protected $requestClass = DestFormRequest::class;

    public function afterCreate(Collection $items)
    {
        $createEvent = $this->queueHandler(Events\DestCreated::class);

        $this->successItems(
            'pkg.backup::destination.created',
            $items->each(function ($item) use ($createEvent) {
                $createEvent($item);
                $this->createFields($item);
            }));
    }

    protected function updateAll(Collection $items)
    {
        $this->setName($items);
        $this->setHandler($items);
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
                ->filter(function ($item) use ($changed) {
                    if (!$changed($item)) {
                        $this->updateFields($item);
                        return false;
                    }

                    if (!$this->create) {
                        $this->restoreFields($item);
                    }

                    return true;
                })
                ->reject([$this, 'isCreating'])
                ->each($event)
        );
    }

    private function restoreFields(Dest $item)
    {
        $item->fieldValues()->delete();
        $this->createFields($item);
    }

    private function createFields(Dest $item)
    {
        $fields = collect($this->input('fields'))
            ->map(function ($value, $id) use ($item) {
                $fieldValue = new Value();

                $fieldValue->field_id = $id;
                $fieldValue->value    = $value['value'];

                return $fieldValue;
            });

        $item->fieldValues()->saveMany($fields);
    }

    private function updateFields(Dest $item)
    {
        $item->fieldValues->each(function ($fieldValue) {

            $changed = $this->changed([
                'value' => $this->input("fields.{$fieldValue->field_id}.value")
            ]);

            $event = $this->queueHandler(FieldValueChanged::class);

            $this->successItems(
                'pkg.backup::destination.update.field_value',
                collect([$fieldValue])
                    ->filter($changed)
                    ->reject([$this, 'isCreating'])
                    ->each(function ($fieldValue) use ($event) {
                        $event($fieldValue);
                        $fieldValue->save();
                    })
            );
        });
    }

}
