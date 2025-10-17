<?php

namespace LaravelUltra\Table\Actions;

class ActionFactory
{
    public static function create($type, $name, $label = null)
    {
        return match($type) {
            'edit' => new EditAction($name, $label),
            'delete' => new DeleteAction($name, $label),
            'view' => new ViewAction($name, $label),
            'bulk_delete' => (new BulkAction($name, $label))->method('delete'),
            'bulk_activate' => (new BulkAction($name, $label))->method('post'),
            'bulk_export' => (new BulkAction($name, $label))->method('get'),
            default => new RowAction($name, $label)
        };
    }

    public static function edit($name = 'edit', $label = null)
    {
        return new EditAction($name, $label);
    }

    public static function delete($name = 'delete', $label = null)
    {
        return new DeleteAction($name, $label);
    }

    public static function view($name = 'view', $label = null)
    {
        return new ViewAction($name, $label);
    }

    public static function bulkDelete($name = 'delete', $label = null)
    {
        return (new BulkAction($name, $label))
            ->method('delete')
            ->confirm('Are you sure you want to delete the selected items?');
    }

    public static function bulkExport($name = 'export', $label = null)
    {
        return (new BulkAction($name, $label))
            ->method('get')
            ->confirm('Export selected items?');
    }
}