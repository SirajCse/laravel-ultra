<?php

namespace LaravelUltra\Table\Actions;

class DeleteAction extends RowAction
{
    protected $type = 'delete';

    public function __construct($name = 'delete', $label = null)
    {
        parent::__construct($name, $label ?? 'Delete');
        $this->icon = '🗑️';
        $this->tooltip = 'Delete this record';
        $this->confirm = true;
        $this->confirmText = 'Are you sure you want to delete this record? This action cannot be undone.';
        $this->method = 'delete';
    }

    public function execute($data)
    {
        return parent::execute($data);
    }
}