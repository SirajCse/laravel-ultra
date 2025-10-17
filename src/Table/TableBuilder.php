<?php

namespace LaravelUltra\Table;

use Inertia\Inertia;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use LaravelUltra\Table\Columns\ColumnFactory;
use LaravelUltra\Table\Actions\ActionFactory;
use LaravelUltra\Table\Filters\FilterFactory;
use LaravelUltra\Table\Views\TableView;

class TableBuilder
{
    protected $source;
    protected $columns = [];
    protected $filters = [];
    protected $rowActions = [];
    protected $bulkActions = [];
    protected $views = [];
    protected $config;
    protected $perPage = 25;
    protected $responseType = 'auto'; // auto, inertia, json, array
    protected $inertiaComponent = 'Ultra/Table';
    protected $searchQuery = '';
    protected $sortColumn = '';
    protected $sortDirection = 'asc';
    protected $selectedView = null;
    protected $withSearch = true;
    protected $withFilters = true;
    protected $withPagination = true;
    protected $stickyHeader = false;
    protected $stickyColumns = [];
    protected $exportable = false;
    protected $emptyState = null;

    public function __construct($source = null, $config = [])
    {
        $this->source = $source;
        $this->config = $config;
        $this->initializeDefaults();
    }

    protected function initializeDefaults()
    {
        $this->perPage = $this->config['table']['default_per_page'] ?? 25;
        $this->withSearch = $this->config['table']['search']['enabled'] ?? true;
        $this->withFilters = $this->config['table']['filters']['enabled'] ?? true;
        $this->withPagination = $this->config['table']['pagination']['enabled'] ?? true;
    }

    // ==================== COLUMN METHODS ====================

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

    public function addImageColumn($key, $label = null)
    {
        $this->columns[] = ColumnFactory::image($key, $label);
        return $this;
    }

    public function addBooleanColumn($key, $label = null)
    {
        $this->columns[] = ColumnFactory::boolean($key, $label);
        return $this;
    }

    public function addNumberColumn($key, $label = null)
    {
        $this->columns[] = ColumnFactory::number($key, $label);
        return $this;
    }

    public function addIconColumn($key, $label = null)
    {
        $this->columns[] = ColumnFactory::icon($key, $label);
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

    public function toggleable($toggleable = true)
    {
        if (count($this->columns) > 0) {
            end($this->columns)->toggleable($toggleable);
        }
        return $this;
    }

    public function sticky()
    {
        if (count($this->columns) > 0) {
            $column = end($this->columns);
            $this->stickyColumns[] = $column->getKey();
        }
        return $this;
    }

    // ==================== FILTER METHODS ====================

    public function addSelectFilter($key, $label = null)
    {
        $this->filters[] = FilterFactory::select($key, $label);
        return $this;
    }

    public function addDateFilter($key, $label = null)
    {
        $this->filters[] = FilterFactory::date($key, $label);
        return $this;
    }

    public function addTextFilter($key, $label = null)
    {
        $this->filters[] = FilterFactory::text($key, $label);
        return $this;
    }

    public function withFilters($enabled = true)
    {
        $this->withFilters = $enabled;
        return $this;
    }

    // ==================== ACTION METHODS ====================

    public function addRowAction($action)
    {
        if (is_string($action)) {
            $action = ActionFactory::create('row', $action);
        }
        $this->rowActions[] = $action;
        return $this;
    }

    public function addBulkAction($action)
    {
        if (is_string($action)) {
            $action = ActionFactory::create('bulk', $action);
        }
        $this->bulkActions[] = $action;
        return $this;
    }

    public function withRowActions($actions = [])
    {
        if (!empty($actions)) {
            foreach ($actions as $action) {
                $this->addRowAction($action);
            }
        } else {
            // Add default actions
            $this->addRowAction(ActionFactory::view());
            $this->addRowAction(ActionFactory::edit());
            $this->addRowAction(ActionFactory::delete());
        }
        return $this;
    }

    public function withBulkActions($actions = [])
    {
        if (!empty($actions)) {
            foreach ($actions as $action) {
                $this->addBulkAction($action);
            }
        } else {
            // Add default bulk actions
            $this->addBulkAction(ActionFactory::bulkDelete());
            $this->addBulkAction(ActionFactory::bulkExport());
        }
        return $this;
    }

    // ==================== VIEW METHODS ====================

    public function addView($name, $key = null)
    {
        $view = new TableView($name, $key);
        $this->views[] = $view;

        // Set first view as default
        if (count($this->views) === 1) {
            $view->default();
            $this->selectedView = $view;
        }

        return $view;
    }

    public function withViews($views = [])
    {
        foreach ($views as $view) {
            if (is_string($view)) {
                $this->addView($view);
            } elseif (is_array($view)) {
                $this->addView($view['name'], $view['key'] ?? null);
            }
        }
        return $this;
    }

    // ==================== FEATURE METHODS ====================

    public function withPagination($perPage = null)
    {
        $this->withPagination = true;
        if ($perPage) {
            $this->perPage = $perPage;
        }
        return $this;
    }

    public function withoutPagination()
    {
        $this->withPagination = false;
        return $this;
    }

    public function withSearch($enabled = true)
    {
        $this->withSearch = $enabled;
        return $this;
    }

    public function withStickyHeader($sticky = true)
    {
        $this->stickyHeader = $sticky;
        return $this;
    }

    public function withExport($formats = ['csv', 'excel'])
    {
        $this->exportable = $formats;
        return $this;
    }

    public function withEmptyState($content = null)
    {
        $this->emptyState = $content ?? [
            'icon' => '📊',
            'title' => 'No data found',
            'description' => 'There are no records to display.',
            'action' => 'Reset Filters'
        ];
        return $this;
    }

    public function withRowLinks($enabled = true)
    {
        $this->config['row_links'] = $enabled;
        return $this;
    }

    // ==================== RESPONSE METHODS ====================

    public function asInertia($component = null)
    {
        $this->responseType = 'inertia';
        if ($component) {
            $this->inertiaComponent = $component;
        }
        return $this;
    }

    public function asJson()
    {
        $this->responseType = 'json';
        return $this;
    }

    public function asArray()
    {
        $this->responseType = 'array';
        return $this;
    }

    public function toInertia($request, $component = null)
    {
        return $this->asInertia($component)->toResponse($request);
    }

    public function toJson($request)
    {
        return $this->asJson()->toResponse($request);
    }

    public function toResponse($request)
    {
        // Apply request parameters
        $this->applyRequestParameters($request);

        // Get processed data
        $data = $this->getData($request);

        // Build response array
        $response = $this->buildResponse($data, $request);

        return $this->formatResponse($response);
    }

    protected function applyRequestParameters($request)
    {
        $this->searchQuery = $request->get('search', '');
        $this->sortColumn = $request->get('sort.column', '');
        $this->sortDirection = $request->get('sort.direction', 'asc');

        // Apply view if specified
        if ($viewKey = $request->get('view')) {
            $this->applyView($viewKey);
        }
    }

    protected function applyView($viewKey)
    {
        foreach ($this->views as $view) {
            if ($view->getKey() === $viewKey) {
                $this->selectedView = $view;
                break;
            }
        }
    }

    protected function getData($request)
    {
        if ($this->source instanceof Builder) {
            return $this->getEloquentData($request);
        }

        if (is_array($this->source) || $this->source instanceof Collection) {
            return $this->getArrayData($request);
        }

        return collect();
    }

    protected function getEloquentData($request)
    {
        $query = $this->source;

        // Apply search
        if ($this->withSearch && $this->searchQuery) {
            $this->applyEloquentSearch($query, $this->searchQuery);
        }

        // Apply filters
        if ($this->withFilters) {
            foreach ($this->filters as $filter) {
                $filterValue = $request->get("filters.{$filter->getKey()}");
                $filter->apply($query, $filterValue);
            }
        }

        // Apply sorting
        if ($this->sortColumn) {
            $query->orderBy($this->sortColumn, $this->sortDirection);
        }

        // Apply view settings
        if ($this->selectedView) {
            $this->applyViewSettings($query);
        }

        if ($this->withPagination) {
            return $query->paginate($this->perPage);
        }

        return $query->get();
    }

    protected function getArrayData($request)
    {
        $data = collect($this->source);

        // Apply search
        if ($this->withSearch && $this->searchQuery) {
            $data = $this->applyArraySearch($data, $this->searchQuery);
        }

        // Apply filters
        if ($this->withFilters) {
            foreach ($this->filters as $filter) {
                $filterValue = $request->get("filters.{$filter->getKey()}");
                $data = $filter->apply($data, $filterValue);
            }
        }

        // Apply sorting
        if ($this->sortColumn) {
            $data = $this->applyArraySort($data, $this->sortColumn, $this->sortDirection);
        }

        // Apply view settings
        if ($this->selectedView) {
            $data = $this->applyViewSettingsToArray($data);
        }

        if ($this->withPagination) {
            $page = $request->get('page', 1);
            $offset = ($page - 1) * $this->perPage;

            return new LengthAwarePaginator(
                $data->slice($offset, $this->perPage)->values(),
                $data->count(),
                $this->perPage,
                $page,
                ['path' => $request->url()]
            );
        }

        return $data;
    }

    protected function applyEloquentSearch($query, $search)
    {
        $searchableColumns = $this->getSearchableColumns();

        if (!empty($searchableColumns)) {
            $query->where(function ($q) use ($searchableColumns, $search) {
                foreach ($searchableColumns as $column) {
                    if (str_contains($column, '.')) {
                        // Relationship search
                        $this->applyRelationshipSearch($q, $column, $search);
                    } else {
                        $q->orWhere($column, 'like', "%{$search}%");
                    }
                }
            });
        }
    }

    protected function applyArraySearch($data, $search)
    {
        $searchableColumns = $this->getSearchableColumns();

        return $data->filter(function ($item) use ($searchableColumns, $search) {
            foreach ($searchableColumns as $column) {
                $value = data_get($item, $column);
                if (stripos($value, $search) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    protected function applyArraySort($data, $column, $direction)
    {
        return $data->sortBy($column, SORT_REGULAR, $direction === 'desc');
    }

    protected function applyRelationshipSearch($query, $column, $search)
    {
        [$relation, $column] = explode('.', $column, 2);

        $query->orWhereHas($relation, function ($q) use ($column, $search) {
            $q->where($column, 'like', "%{$search}%");
        });
    }

    protected function applyViewSettings($query)
    {
        if ($this->selectedView->getSort()) {
            $sort = $this->selectedView->getSort();
            $query->orderBy($sort['column'], $sort['direction']);
        }

        if ($viewPerPage = $this->selectedView->getPerPage()) {
            $this->perPage = $viewPerPage;
        }
    }

    protected function applyViewSettingsToArray($data)
    {
        if ($this->selectedView->getSort()) {
            $sort = $this->selectedView->getSort();
            $data = $data->sortBy($sort['column'], SORT_REGULAR, $sort['direction'] === 'desc');
        }

        if ($viewPerPage = $this->selectedView->getPerPage()) {
            $this->perPage = $viewPerPage;
        }

        return $data;
    }

    protected function getSearchableColumns()
    {
        return collect($this->columns)
            ->filter(fn($column) => $column->isSearchable())
            ->pluck('key')
            ->toArray();
    }

    protected function buildResponse($data, $request)
    {
        $items = $data instanceof LengthAwarePaginator ? $data->items() : $data->toArray();

        return [
            'data' => $items,
            'columns' => $this->getColumnsConfig(),
            'filters' => $this->getFiltersConfig(),
            'actions' => [
                'row' => $this->getRowActionsConfig(),
                'bulk' => $this->getBulkActionsConfig(),
            ],
            'views' => $this->getViewsConfig(),
            'meta' => $this->getMetaData($data, $request),
            'config' => $this->getTableConfig(),
        ];
    }

    protected function getColumnsConfig()
    {
        $columns = collect($this->columns)->map->toArray();

        // Apply view column visibility
        if ($this->selectedView && $this->selectedView->getColumns()) {
            $viewColumns = $this->selectedView->getColumns();
            $columns = $columns->map(function ($column) use ($viewColumns) {
                $column['visible'] = in_array($column['key'], $viewColumns);
                return $column;
            });
        }

        return $columns->all();
    }

    protected function getFiltersConfig()
    {
        return collect($this->filters)->map->toArray()->all();
    }

    protected function getRowActionsConfig()
    {
        return collect($this->rowActions)->map->toArray()->all();
    }

    protected function getBulkActionsConfig()
    {
        return collect($this->bulkActions)->map->toArray()->all();
    }

    protected function getViewsConfig()
    {
        return collect($this->views)->map->toArray()->all();
    }

    protected function getMetaData($data, $request)
    {
        $meta = [
            'search' => $this->searchQuery,
            'sort' => [
                'column' => $this->sortColumn,
                'direction' => $this->sortDirection,
            ],
            'current_view' => $this->selectedView ? $this->selectedView->getKey() : null,
        ];

        if ($this->withPagination && $data instanceof LengthAwarePaginator) {
            $meta['pagination'] = [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ];
        }

        return $meta;
    }

    protected function getTableConfig()
    {
        return [
            'features' => [
                'search' => $this->withSearch,
                'filters' => $this->withFilters,
                'pagination' => $this->withPagination,
                'sticky_header' => $this->stickyHeader,
                'sticky_columns' => $this->stickyColumns,
                'exportable' => $this->exportable,
                'row_links' => $this->config['row_links'] ?? false,
                'toggleable_columns' => $this->config['table']['toggleable_columns'] ?? true,
            ],
            'empty_state' => $this->emptyState,
        ];
    }

    protected function formatResponse($response)
    {
        if ($this->responseType === 'auto') {
            $this->responseType = class_exists('Inertia\Inertia') ? 'inertia' : 'array';
        }

        return match($this->responseType) {
            'inertia' => Inertia::render($this->inertiaComponent, $response),
            'json' => new JsonResponse($response),
            default => $response
        };
    }

    // ==================== UTILITY METHODS ====================

    public static function for($source)
    {
        return new static($source);
    }

    public static function fromModel($model)
    {
        if (is_string($model)) {
            $model = app($model)->query();
        }
        return new static($model);
    }

    public function getQuery()
    {
        return $this->source instanceof Builder ? $this->source : null;
    }

    public function getConfig()
    {
        return $this->config;
    }
}