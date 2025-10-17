<?php

namespace LaravelUltra\Table\Views;

class TableView
{
    protected $name;
    protected $key;
    protected $columns = [];
    protected $filters = [];
    protected $sort = [];
    protected $perPage;
    protected $default = false;

    public function __construct($name, $key = null)
    {
        $this->name = $name;
        $this->key = $key ?? str_slug($name);
    }

    public function columns($columns)
    {
        $this->columns = $columns;
        return $this;
    }

    public function filters($filters)
    {
        $this->filters = $filters;
        return $this;
    }

    public function sort($column, $direction = 'asc')
    {
        $this->sort = compact('column', 'direction');
        return $this;
    }

    public function perPage($perPage)
    {
        $this->perPage = $perPage;
        return $this;
    }

    public function default($default = true)
    {
        $this->default = $default;
        return $this;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'key' => $this->key,
            'columns' => $this->columns,
            'filters' => $this->filters,
            'sort' => $this->sort,
            'per_page' => $this->perPage,
            'default' => $this->default,
        ];
    }
}