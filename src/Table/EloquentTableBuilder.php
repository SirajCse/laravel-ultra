<?php

namespace LaravelUltra\Table;

use Inertia\Inertia;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelUltra\Table\Columns\ColumnFactory;
use LaravelUltra\Table\Actions\ActionFactory;
use LaravelUltra\Table\Filters\FilterFactory;
use LaravelUltra\Table\Views\TableView;

class EloquentTableBuilder extends TableBuilder
{
    protected $model;
    protected $resourceClass;
    protected $relationships = [];
    protected $withCount = [];
    protected $withAvg = [];
    protected $withSum = [];
    protected $withMax = [];
    protected $withMin = [];
    protected $scopes = [];
    protected $globalScopes = [];
    protected $softDeletes = false;

    public function __construct($model, $config = [])
    {
        $this->model = $model instanceof Builder ? $model->getModel() : $model;
        parent::__construct($model, $config);

        // Auto-detect soft deletes
        $this->softDeletes = method_exists($this->model, 'bootSoftDeletes');
    }

    // ==================== ELOQUENT SPECIFIC METHODS ====================

    public function withResource($resourceClass)
    {
        $this->resourceClass = $resourceClass;
        return $this;
    }

    public function withRelations($relations)
    {
        $this->relationships = is_array($relations) ? $relations : func_get_args();
        return $this;
    }

    public function withCount($relations)
    {
        $this->withCount = is_array($relations) ? $relations : func_get_args();
        return $this;
    }

    public function withAvg($relation, $column)
    {
        $this->withAvg[] = compact('relation', 'column');
        return $this;
    }

    public function withSum($relation, $column)
    {
        $this->withSum[] = compact('relation', 'column');
        return $this;
    }

    public function withMax($relation, $column)
    {
        $this->withMax[] = compact('relation', 'column');
        return $this;
    }

    public function withMin($relation, $column)
    {
        $this->withMin[] = compact('relation', 'column');
        return $this;
    }

    public function withScope($scope, ...$parameters)
    {
        $this->scopes[] = compact('scope', 'parameters');
        return $this;
    }

    public function withoutGlobalScopes($scopes = null)
    {
        if ($scopes === null) {
            $this->globalScopes = 'all';
        } else {
            $this->globalScopes = is_array($scopes) ? $scopes : func_get_args();
        }
        return $this;
    }

    public function withTrashed()
    {
        if ($this->softDeletes) {
            $this->source = $this->getQuery()->withTrashed();
        }
        return $this;
    }

    public function onlyTrashed()
    {
        if ($this->softDeletes) {
            $this->source = $this->getQuery()->onlyTrashed();
        }
        return $this;
    }

    // ==================== OVERRIDDEN METHODS ====================

    public function toResponse($request)
    {
        // Apply request parameters
        $this->applyRequestParameters($request);

        // Build Eloquent query
        $query = $this->buildEloquentQuery();

        // Apply Eloquent operations
        $data = $this->applyEloquentOperations($query, $request);

        // Build response
        $response = $this->buildEloquentResponse($data, $request);

        return $this->formatResponse($response);
    }

    protected function buildEloquentQuery()
    {
        $query = $this->source instanceof Builder ? $this->source : $this->model->query();

        // Remove global scopes if specified
        if ($this->globalScopes === 'all') {
            $query->withoutGlobalScopes();
        } elseif (!empty($this->globalScopes)) {
            $query->withoutGlobalScopes($this->globalScopes);
        }

        // Apply scopes
        foreach ($this->scopes as $scope) {
            $query->{$scope['scope']}(...$scope['parameters']);
        }

        // Eager load relationships
        if (!empty($this->relationships)) {
            $query->with($this->relationships);
        }

        // Add counts
        if (!empty($this->withCount)) {
            $query->withCount($this->withCount);
        }

        // Add averages
        foreach ($this->withAvg as $avg) {
            $query->withAvg($avg['relation'], $avg['column']);
        }

        // Add sums
        foreach ($this->withSum as $sum) {
            $query->withSum($sum['relation'], $sum['column']);
        }

        // Add max
        foreach ($this->withMax as $max) {
            $query->withMax($max['relation'], $max['column']);
        }

        // Add min
        foreach ($this->withMin as $min) {
            $query->withMin($min['relation'], $min['column']);
        }

        return $query;
    }

    protected function applyEloquentOperations($query, $request)
    {
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
            $this->applyEloquentSort($query, $this->sortColumn, $this->sortDirection);
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

    protected function applyEloquentSort($query, $column, $direction)
    {
        // Handle relationship sorting: user.name
        if (str_contains($column, '.')) {
            $this->applyRelationshipSort($query, $column, $direction);
        } else {
            $query->orderBy($column, $direction);
        }
    }

    protected function applyRelationshipSort($query, $column, $direction)
    {
        $parts = explode('.', $column);
        $sortColumn = array_pop($parts);
        $relationship = implode('.', $parts);

        $query->with([$relationship => function ($query) use ($sortColumn, $direction) {
            $query->orderBy($sortColumn, $direction);
        }]);

        // For proper sorting, we might need to join or use subqueries
        // This is a simplified version - you might want to implement
        // a more sophisticated relationship sorting mechanism
    }

    protected function applyRelationshipSearch($query, $column, $search)
    {
        $parts = explode('.', $column);
        $searchColumn = array_pop($parts);
        $relationship = implode('.', $parts);

        $query->orWhereHas($relationship, function ($q) use ($searchColumn, $search) {
            $q->where($searchColumn, 'like', "%{$search}%");
        });
    }

    protected function applyViewSettings($query)
    {
        if ($this->selectedView->getSort()) {
            $sort = $this->selectedView->getSort();
            $this->applyEloquentSort($query, $sort['column'], $sort['direction']);
        }

        if ($viewPerPage = $this->selectedView->getPerPage()) {
            $this->perPage = $viewPerPage;
        }

        // Apply view filters
        if ($viewFilters = $this->selectedView->getFilters()) {
            foreach ($viewFilters as $filterKey => $filterValue) {
                $filter = collect($this->filters)->first(fn($f) => $f->getKey() === $filterKey);
                if ($filter) {
                    $filter->apply($query, $filterValue);
                }
            }
        }
    }

    // ==================== RESPONSE BUILDING ====================

    protected function buildEloquentResponse($data, $request)
    {
        $items = $this->transformEloquentData($data);

        return [
            'data' => $items,
            'columns' => $this->getColumnsConfig(),
            'filters' => $this->getFiltersConfig(),
            'actions' => [
                'row' => $this->getRowActionsConfig(),
                'bulk' => $this->getBulkActionsConfig(),
            ],
            'views' => $this->getViewsConfig(),
            'meta' => $this->getEloquentMetaData($data, $request),
            'config' => $this->getTableConfig(),
            'eloquent' => $this->getEloquentMeta(),
        ];
    }

    protected function transformEloquentData($data)
    {
        $collection = $data instanceof LengthAwarePaginator ? $data->getCollection() : $data;

        if ($this->resourceClass) {
            return $this->resourceClass::collection($collection)->toArray(request());
        }

        // Transform with relationships and aggregates
        $transformed = $collection->map(function ($model) {
            $data = $model->toArray();

            // Add relationship data
            foreach ($this->relationships as $relation) {
                if ($model->relationLoaded($relation)) {
                    $data[$relation] = $model->$relation;
                }
            }

            // Add count data
            foreach ($this->withCount as $relation) {
                $countKey = "{$relation}_count";
                if (isset($model->$countKey)) {
                    $data[$countKey] = $model->$countKey;
                }
            }

            // Add aggregate data
            foreach (['avg', 'sum', 'max', 'min'] as $aggregate) {
                $property = "with{$aggregate}";
                foreach ($this->$property as $agg) {
                    $aggKey = "{$agg['relation']}_{$agg['column']}_{$aggregate}";
                    if (isset($model->$aggKey)) {
                        $data[$aggKey] = $model->$aggKey;
                    }
                }
            }

            return $data;
        });

        if ($data instanceof LengthAwarePaginator) {
            $data->setCollection($transformed);
            return $data->items();
        }

        return $transformed->toArray();
    }

    protected function getEloquentMetaData($data, $request)
    {
        $meta = parent::getMetaData($data, $request);

        // Add Eloquent-specific meta
        $meta['eloquent'] = [
            'model' => get_class($this->model),
            'relationships' => $this->relationships,
            'aggregates' => [
                'counts' => $this->withCount,
                'averages' => $this->withAvg,
                'sums' => $this->withSum,
                'maximums' => $this->withMax,
                'minimums' => $this->withMin,
            ],
            'soft_deletes' => $this->softDeletes,
            'total_relations' => count($this->relationships),
        ];

        if ($data instanceof LengthAwarePaginator) {
            $meta['eloquent']['query_time'] = null; // You can add query execution time here
        }

        return $meta;
    }

    protected function getEloquentMeta()
    {
        return [
            'model' => get_class($this->model),
            'table' => $this->model->getTable(),
            'primary_key' => $this->model->getKeyName(),
            'relationships_loaded' => $this->relationships,
            'aggregates_loaded' => [
                'counts' => $this->withCount,
                'averages' => $this->withAvg,
                'sums' => $this->withSum,
                'maximums' => $this->withMax,
                'minimums' => $this->withMin,
            ],
        ];
    }

    // ==================== UTILITY METHODS ====================

    public function getQuery()
    {
        return $this->source instanceof Builder ? $this->source : $this->model->query();
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getModelClass()
    {
        return get_class($this->model);
    }

    public function getTableName()
    {
        return $this->model->getTable();
    }

    public function getRelationships()
    {
        return $this->relationships;
    }

    public function getAggregates()
    {
        return [
            'counts' => $this->withCount,
            'averages' => $this->withAvg,
            'sums' => $this->withSum,
            'maximums' => $this->withMax,
            'minimums' => $this->withMin,
        ];
    }

    // ==================== STATIC CONSTRUCTORS ====================

    public static function forModel($model, $config = [])
    {
        if (is_string($model)) {
            $model = app($model);
        }
        return new static($model, $config);
    }

    public static function forQuery(Builder $query, $config = [])
    {
        return new static($query, $config);
    }

    public static function fromRelationship(Relation $relation, $config = [])
    {
        return new static($relation->getQuery(), $config);
    }

    // ==================== ADVANCED ELOQUENT FEATURES ====================

    public function withSubSelect($column, $query)
    {
        if ($this->source instanceof Builder) {
            $this->source->addSelect([$column => $query]);
        }
        return $this;
    }

    public function withExists($relation)
    {
        if ($this->source instanceof Builder) {
            $this->source->withExists($relation);
        }
        return $this;
    }

    public function withAggregate($relation, $column, $aggregate = 'count')
    {
        if ($this->source instanceof Builder) {
            $this->source->withAggregate($relation, $column, $aggregate);
        }
        return $this;
    }

    public function withCasts($casts)
    {
        if ($this->source instanceof Builder) {
            $this->source->withCasts($casts);
        }
        return $this;
    }

    public function withSelect($columns = ['*'])
    {
        if ($this->source instanceof Builder) {
            $this->source->select($columns);
        }
        return $this;
    }

    public function withGroupBy($groups)
    {
        if ($this->source instanceof Builder) {
            $this->source->groupBy($groups);
        }
        return $this;
    }

    public function withHaving($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($this->source instanceof Builder) {
            $this->source->having($column, $operator, $value, $boolean);
        }
        return $this;
    }

    // ==================== PERFORMANCE OPTIMIZATIONS ====================

    public function withoutEagerLoads()
    {
        if ($this->source instanceof Builder) {
            $this->source->setEagerLoads([]);
        }
        return $this;
    }

    public function withCursorPaginate($perPage = null)
    {
        $this->withPagination = true;
        $this->perPage = $perPage ?? $this->perPage;

        // Override the pagination method in applyEloquentOperations
        $this->config['pagination_method'] = 'cursor';

        return $this;
    }

    public function withSimplePaginate($perPage = null)
    {
        $this->withPagination = true;
        $this->perPage = $perPage ?? $this->perPage;

        // Override the pagination method in applyEloquentOperations
        $this->config['pagination_method'] = 'simple';

        return $this;
    }

    public function chunk($chunkSize = 1000, callable $callback)
    {
        $query = $this->buildEloquentQuery();
        $query->chunk($chunkSize, $callback);
        return $this;
    }

    public function lazy($chunkSize = 1000)
    {
        $query = $this->buildEloquentQuery();
        return $query->lazy($chunkSize);
    }
}