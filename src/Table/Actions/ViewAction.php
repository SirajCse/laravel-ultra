<?php

namespace LaravelUltra\Table\Actions;

class ViewAction extends RowAction
{
    protected $type = 'view';

    public function __construct($name = 'view', $label = null)
    {
        parent::__construct($name, $label ?? 'View');
        $this->icon = '👁️';
        $this->tooltip = 'View this record';
        $this->method = 'get';
    }

    public function execute($data)
    {
        $model = $data['model'] ?? null;
        $id = $data['id'] ?? null;

        if ($model && $id) {
            $record = $model->find($id);

            return [
                'success' => true,
                'action' => 'redirect',
                'url' => $this->url ?: $this->generateViewUrl($record),
                'method' => 'get',
                'record' => $record
            ];
        }

        return [
            'success' => false,
            'message' => 'Unable to perform view action.'
        ];
    }

    protected function generateViewUrl($record)
    {
        $modelName = class_basename(get_class($record));
        $routeName = strtolower($modelName) . '.show';

        if (route()->has($routeName)) {
            return route($routeName, $record);
        }

        return url('/' . strtolower($modelName) . '/' . $record->id);
    }
}