<?php

namespace LaravelUltra\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use LaravelUltra\Table\Columns\ColumnFactory;

class TableBuilder
{
    protected $source;
    protected $columns = [];
    protected $filters = [];
    protected $actions = [];
    protected $config;
    protected $perPage = 25;

    public function __construct($source = null, $config = [])
    {
        $this->source = $source;
        $this->config = $config;
    }

    public static function for($source)
    {
        return new static($source);
    }

    public function addTextColumn($key, $label = null)
    {
        $this->columns[] = ColumnFactory::text($key, $label);
        return $this;
    }

    public function addEmailColumn($key, $label = null)
    {
        $this->columns[] = ColumnFactory::email($key, $label);
        return $this;
    }

    public function addDateColumn($key, $label = null)
    {
        $this->columns[] = ColumnFactory::date($key, $label);
        return $this;
    }

    public function sortable($column = null)
    {
        if ($column && count($this->columns) > 0) {
            end($this->columns)->sortable();
        }
        return $this;
    }

    public function searchable($column = null)
    {
        if ($column && count($this->columns) > 0) {
            end($this->columns)->searchable();
        }
        return $this;
    }

    public function withPagination($perPage = null)
    {
        $this->perPage = $perPage ?? $this->config['table']['default_per_page'] ?? 25;
        return $this;
    }

    public function toResponse($request)
    {
        $data = $this->getData($request);

        return [
            'data' => $data,
            'columns' => $this->getColumnsConfig(),
            'meta' => [
                'pagination' => $this->getPagination($data),
                'sort' => $request->get('sort', []),
                'filters' => $request->get('filters', []),
            ]
        ];
    }

    protected function getData($request)
    {
        if ($this->source instanceof Builder) {
            return $this->getEloquentData($request);
        }

        if (is_array($this->source)) {
            return $this->getArrayData($request);
        }

        return collect();
    }

    protected function getEloquentData($request)
    {
        $query = $this->source;

        // Apply sorting
        if ($sort = $request->get('sort')) {
            $query->orderBy($sort['column'], $sort['direction'] ?? 'asc');
        }

        // Apply search
        if ($search = $request->get('search')) {
            $this->applySearch($query, $search);
        }

        return $query->paginate($this->perPage);
    }

    protected function applySearch($query, $search)
    {
        $searchableColumns = collect($this->columns)
            ->filter(fn($column) => $column->isSearchable())
            ->pluck('key');

        if ($searchableColumns->isNotEmpty()) {
            $query->where(function ($q) use ($searchableColumns, $search) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }
    }

    protected function getColumnsConfig()
    {
        return collect($this->columns)->map->toArray()->all();
    }

    protected function getPagination($data)
    {
        if (method_exists($data, 'total')) {
            return [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ];
        }

        return null;
    }
}