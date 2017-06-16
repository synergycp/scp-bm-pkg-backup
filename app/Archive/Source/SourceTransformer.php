<?php

namespace Packages\Backup\App\Archive\Source;

use App\Api\Transformer;

class SourceTransformer extends Transformer
{
    /**
     * @param Source $item
     * @return mixed
     */
    public function item(Source $item)
    {
        return $item->expose(['id', 'name', 'ext']) + $this->handler($item);
    }

    private function handler(Source $item)
    {
        return [
            'handler' => $item->handler->expose(['name']) + ['fields'  => $this->fields($item)],
        ];
    }

    private function fields(Source $item)
    {
        $fields = [];

        $item->fieldValues->each(function ($field) use (&$fields) {
            $fields[$field->field_id] = [
                'id'    => $field->field_id,
                'name'  => $field->field->name,
                'value' => $field->value,
            ];
        });

        return $fields;
    }

    public function resource(Source $item)
    {
        return $this->item($item) + [];
    }

}
