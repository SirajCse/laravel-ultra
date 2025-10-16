<?php

namespace LaravelUltra\Table\Columns;

abstract class Column
{
    protected $key;
    protected $label;
    protected $sortable = false;
    protected $searchable = false;
    protected $visible = true;

    public function __construct($key, $label = null)
    {
        $this->key = $key;
        $this->label = $label ?? ucfirst(str_replace('_', ' ', $key));
    }

    public function sortable($sortable = true)
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function searchable($searchable = true)
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function label($label)
    {
        $this->label = $label;
        return $this;
    }

    public function isSortable()
    {
        return $this->sortable;
    }

    public function isSearchable()
    {
        return $this->searchable;
    }

    public function toArray()
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'sortable' => $this->sortable,
            'searchable' => $this->searchable,
            'type' => $this->getType(),
        ];
    }

    abstract protected function getType();
}