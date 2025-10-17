<?php

namespace LaravelUltra\Table\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkAction extends Action
{
    protected $type = 'bulk';
    protected $endpoint;
    protected $method = 'post';
    protected $requiresSelection = true;
    protected $confirm = true;
    protected $confirmText = 'Are you sure you want to perform this action on selected items?';
    protected $handler;

    public function endpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function method($method)
    {
        $this->method = $method;
        return $this;
    }

    public function requiresSelection($requires = true)
    {
        $this->requiresSelection = $requires;
        return $this;
    }

    public function confirm($text = null)
    {
        $this->confirm = true;
        $this->confirmText = $text ?? $this->confirmText;
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
        $ids = $data['ids'] ?? [];
        $modelClass = $data['model'] ?? null;

        if (empty($ids) || !$modelClass) {
            return [
                'success' => false,
                'message' => 'No items selected or model not specified.'
            ];
        }

        try {
            DB::beginTransaction();

            $results = [];
            $model = app($modelClass);

            switch ($this->name) {
                case 'delete':
                    $results = $this->executeDelete($model, $ids);
                    break;

                case 'activate':
                    $results = $this->executeActivate($model, $ids);
                    break;

                case 'deactivate':
                    $results = $this->executeDeactivate($model, $ids);
                    break;

                case 'export':
                    $results = $this->executeExport($model, $ids);
                    break;

                case 'archive':
                    $results = $this->executeArchive($model, $ids);
                    break;

                default:
                    $results = $this->executeCustom($model, $ids);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => $this->getSuccessMessage(count($ids)),
                'data' => $results
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Action failed: ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getTrace() : null
            ];
        }
    }

    protected function executeDelete($model, $ids)
    {
        $deleted = $model->whereIn('id', $ids)->delete();

        return [
            'deleted_count' => $deleted,
            'ids' => $ids
        ];
    }

    protected function executeActivate($model, $ids)
    {
        $updated = $model->whereIn('id', $ids)->update(['active' => true]);

        return [
            'activated_count' => $updated,
            'ids' => $ids
        ];
    }

    protected function executeDeactivate($model, $ids)
    {
        $updated = $model->whereIn('id', $ids)->update(['active' => false]);

        return [
            'deactivated_count' => $updated,
            'ids' => $ids
        ];
    }

    protected function executeArchive($model, $ids)
    {
        $updated = $model->whereIn('id', $ids)->update(['archived' => true]);

        return [
            'archived_count' => $updated,
            'ids' => $ids
        ];
    }

    protected function executeExport($model, $ids)
    {
        $records = $model->whereIn('id', $ids)->get();

        // Generate export file (CSV, Excel, etc.)
        $filename = $this->generateExportFile($records);

        return [
            'exported_count' => $records->count(),
            'filename' => $filename,
            'download_url' => url("/download/{$filename}")
        ];
    }

    protected function executeCustom($model, $ids)
    {
        // Custom action implementation
        return [
            'processed_count' => count($ids),
            'ids' => $ids
        ];
    }

    protected function generateExportFile($records)
    {
        // Implementation for generating export files
        // This could generate CSV, Excel, PDF, etc.
        $filename = 'export_' . time() . '.csv';

        // Simple CSV generation
        $handle = fopen(storage_path("app/exports/{$filename}"), 'w');

        // Add headers
        if ($records->count() > 0) {
            fputcsv($handle, array_keys($records->first()->toArray()));
        }

        // Add data
        foreach ($records as $record) {
            fputcsv($handle, $record->toArray());
        }

        fclose($handle);

        return $filename;
    }

    protected function getSuccessMessage($count)
    {
        $messages = [
            'delete' => "Successfully deleted {$count} items.",
            'activate' => "Successfully activated {$count} items.",
            'deactivate' => "Successfully deactivated {$count} items.",
            'archive' => "Successfully archived {$count} items.",
            'export' => "Successfully exported {$count} items.",
        ];

        return $messages[$this->name] ?? "Successfully processed {$count} items.";
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'endpoint' => $this->endpoint,
            'method' => $this->method,
            'requires_selection' => $this->requiresSelection,
            'confirm' => $this->confirm,
            'confirm_text' => $this->confirmText,
        ]);
    }
}