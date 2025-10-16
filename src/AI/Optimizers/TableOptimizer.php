<?php

namespace LaravelUltra\AI\Optimizers;

use LaravelUltra\AI\AIService;

class TableOptimizer
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function optimizeTableStructure($tableBuilder, $dataSample)
    {
        $suggestions = $this->aiService->suggestTableColumns($dataSample);

        foreach ($suggestions['columns'] ?? [] as $suggestion) {
            $tableBuilder->addColumn(
                $suggestion['type'] ?? 'text',
                $suggestion['key'],
                $suggestion['label'] ?? null
            );

            if ($suggestion['sortable'] ?? false) {
                $tableBuilder->sortable();
            }

            if ($suggestion['searchable'] ?? false) {
                $tableBuilder->searchable();
            }
        }

        return $tableBuilder;
    }

    public function suggestDataVisualizations($data, $columns)
    {
        return $this->aiService->predictTrends([
            'data' => $data,
            'columns' => $columns
        ]);
    }
}