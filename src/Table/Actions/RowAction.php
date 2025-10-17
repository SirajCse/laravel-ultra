<?php

namespace LaravelUltra\Table\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RowAction extends Action
{
    protected $type = 'row';
    protected $url;
    protected $method = 'get';
    protected $icon;
    protected $tooltip;
    protected $confirm = false;
    protected $confirmText = 'Are you sure?';
    protected $shouldOpenInNewTab = false;
    protected $handler;

    public function url($url)
    {
        $this->url = $url;
        return $this;
    }

    public function method($method)
    {
        $this->method = $method;
        return $this;
    }

    public function icon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    public function tooltip($tooltip)
    {
        $this->tooltip = $tooltip;
        return $this;
    }

    public function confirm($text = null)
    {
        $this->confirm = true;
        $this->confirmText = $text ?? $this->confirmText;
        return $this;
    }

    public function openInNewTab($open = true)
    {
        $this->shouldOpenInNewTab = $open;
        return $this;
    }

    public function handle(callable $handler)
    {
        $this->handler = $handler;
        return $this;
    }

    public function execute($data)
    {
        if ($this->handler) {
            return call_user_func($this->handler, $data);
        }

        return $this->defaultExecute($data);
    }

    protected function defaultExecute($data)
    {
        $model = $data['model'] ?? null;
        $id = $data['id'] ?? null;

        if (!$model || !$id) {
            return [
                'success' => false,
                'message' => 'Model or ID not provided.'
            ];
        }

        try {
            DB::beginTransaction();

            $result = [];
            $record = $model->find($id);

            if (!$record) {
                return [
                    'success' => false,
                    'message' => 'Record not found.'
                ];
            }

            switch ($this->name) {
                case 'edit':
                    $result = $this->executeEdit($record, $data);
                    break;

                case 'delete':
                    $result = $this->executeDelete($record, $data);
                    break;

                case 'view':
                    $result = $this->executeView($record, $data);
                    break;

                case 'clone':
                    $result = $this->executeClone($record, $data);
                    break;

                case 'activate':
                    $result = $this->executeActivate($record, $data);
                    break;

                case 'deactivate':
                    $result = $this->executeDeactivate($record, $data);
                    break;

                default:
                    $result = $this->executeCustom($record, $data);
            }

            DB::commit();

            return array_merge([
                'success' => true,
                'message' => $this->getSuccessMessage(),
                'record' => $record
            ], $result);

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Action failed: ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getTrace() : null
            ];
        }
    }

    protected function executeEdit($record, $data)
    {
        // For edit, typically we return the edit URL
        // The actual editing happens in a form/modal
        return [
            'action' => 'redirect',
            'url' => $this->url ?: route($this->getDefaultRoute('edit'), $record),
            'method' => 'get'
        ];
    }

    protected function executeDelete($record, $data)
    {
        $record->delete();

        return [
            'action' => 'refresh',
            'message' => 'Record deleted successfully.'
        ];
    }

    protected function executeView($record, $data)
    {
        return [
            'action' => 'redirect',
            'url' => $this->url ?: route($this->getDefaultRoute('show'), $record),
            'method' => 'get'
        ];
    }

    protected function executeClone($record, $data)
    {
        $newRecord = $record->replicate();
        $newRecord->save();

        return [
            'action' => 'refresh',
            'message' => 'Record cloned successfully.',
            'new_id' => $newRecord->id
        ];
    }

    protected function executeActivate($record, $data)
    {
        $record->update(['active' => true]);

        return [
            'action' => 'refresh',
            'message' => 'Record activated successfully.'
        ];
    }

    protected function executeDeactivate($record, $data)
    {
        $record->update(['active' => false]);

        return [
            'action' => 'refresh',
            'message' => 'Record deactivated successfully.'
        ];
    }

    protected function executeCustom($record, $data)
    {
        // Custom action implementation
        return [
            'action' => 'refresh',
            'message' => 'Action completed successfully.'
        ];
    }

    protected function getDefaultRoute($action)
    {
        $modelName = class_basename($this->getModelClass());
        return strtolower($modelName) . ".{$action}";
    }

    protected function getModelClass()
    {
        // Extract model class from the data or context
        return $this->modelClass ?? 'App\\Models\\User';
    }

    protected function getSuccessMessage()
    {
        $messages = [
            'edit' => 'Redirecting to edit page...',
            'delete' => 'Record deleted successfully.',
            'view' => 'Redirecting to view page...',
            'clone' => 'Record cloned successfully.',
            'activate' => 'Record activated successfully.',
            'deactivate' => 'Record deactivated successfully.',
        ];

        return $messages[$this->name] ?? 'Action completed successfully.';
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'url' => $this->url,
            'method' => $this->method,
            'icon' => $this->icon,
            'tooltip' => $this->tooltip,
            'confirm' => $this->confirm,
            'confirm_text' => $this->confirmText,
            'open_in_new_tab' => $this->shouldOpenInNewTab,
        ]);
    }
}