<?php

namespace LaravelUltra\Table;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TableBuilder
{
    protected $source;
    protected $columns = [];
    protected $config;
    protected $perPage = 25;

    public function __construct($source = null, $config = [])
    {
        $this->source = $source;
        $this->config = $config;
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

    public function sortable()
    {
        if (count($this->columns) > 0) {
            end($this->columns)->sortable();
        }
        return $this;
    }

    public function searchable()
    {
        if (count($this->columns) > 0) {
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
            'data' => $data->items(),
            'columns' => $this->getColumnsConfig(),
            'meta' => [
                'pagination' => [
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                ]
            ]
        ];
    }

    protected function getData($request)
    {
        $data = collect($this->source);

        // Apply simple pagination
        $page = $request->get('page', 1);
        $perPage = $this->perPage;
        $offset = ($page - 1) * $perPage;

        return new LengthAwarePaginator(
            $data->slice($offset, $perPage)->values(),
            $data->count(),
            $perPage,
            $page,
            ['path' => $request->url()]
        );
    }

    protected function getColumnsConfig()
    {
        return collect($this->columns)->map->toArray()->all();
    }
}