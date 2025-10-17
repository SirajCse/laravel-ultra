<?php

namespace LaravelUltra\Table\Actions;

class EditAction extends RowAction
{
    protected $type = 'edit';

    public function __construct($name = 'edit', $label = null)
    {
        parent::__construct($name, $label ?? 'Edit');
        $this->icon = '✏️';
        $this->tooltip = 'Edit this record';
    }

    public function execute($data)
    {
        // For edit actions, we typically return the edit URL
        // The actual editing happens in a form/modal
        $model = $data['model'] ?? null;
        $id = $data['id'] ?? null;

        if ($model && $id) {
            $record = $model->find($id);

            return [
                'success' => true,
                'action' => 'redirect',
                'url' => $this->url ?: $this->generateEditUrl($record),
                'method' => 'get',
                'record' => $record
            ];
        }

        return [
            'success' => false,
            'message' => 'Unable to perform edit action.'
        ];
    }

    protected function generateEditUrl($record)
    {
        // Generate default edit URL based on model
        $modelName = class_basename(get_class($record));
        $routeName = strtolower($modelName) . '.edit';

        if (route()->has($routeName)) {
            return route($routeName, $record);
        }

        // Fallback URL
        return url('/' . strtolower($modelName) . '/' . $record->id . '/edit');
    }
}