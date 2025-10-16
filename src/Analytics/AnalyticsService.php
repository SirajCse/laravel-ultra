<?php

namespace LaravelUltra\Analytics;

use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    protected $config;
    protected $enabled;

    public function __construct($config)
    {
        $this->config = $config;
        $this->enabled = $config['enabled'] ?? false;
    }

    public function trackTableInteraction($tableId, $action, $data = [])
    {
        if (!$this->enabled) return;

        DB::table('ultra_analytics')->insert([
            'session_id' => session()->getId(),
            'component_type' => 'table',
            'component_name' => $tableId,
            'event_type' => $action,
            'event_data' => json_encode($data),
            'performance_metrics' => $this->capturePerformanceMetrics(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function trackFormInteraction($formId, $action, $data = [])
    {
        if (!$this->enabled) return;

        DB::table('ultra_analytics')->insert([
            'session_id' => session()->getId(),
            'component_type' => 'form',
            'component_name' => $formId,
            'event_type' => $action,
            'event_data' => json_encode($data),
            'performance_metrics' => $this->capturePerformanceMetrics(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function getTableAnalytics($tableId, $period = '7d')
    {
        if (!$this->enabled) return [];

        return DB::table('ultra_analytics')
            ->where('component_type', 'table')
            ->where('component_name', $tableId)
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('event_type, COUNT(*) as count, DATE(created_at) as date')
            ->groupBy('event_type', 'date')
            ->get()
            ->groupBy('event_type');
    }

    public function getUserBehaviorInsights($userId = null)
    {
        if (!$this->enabled) return [];

        $query = DB::table('ultra_analytics')
            ->where('created_at', '>=', now()->subDays(30));

        if ($userId) {
            // Join with users table if user tracking is enabled
        }

        return $query->selectRaw('
                component_type,
                event_type,
                COUNT(*) as total_actions,
                AVG(JSON_EXTRACT(performance_metrics, "$.response_time")) as avg_response_time
            ')
            ->groupBy('component_type', 'event_type')
            ->get();
    }

    public function getPerformanceMetrics($componentType = null)
    {
        $query = DB::table('ultra_analytics');

        if ($componentType) {
            $query->where('component_type', $componentType);
        }

        return $query->selectRaw('
                AVG(JSON_EXTRACT(performance_metrics, "$.response_time")) as avg_response_time,
                AVG(JSON_EXTRACT(performance_metrics, "$.memory_usage")) as avg_memory_usage,
                MAX(JSON_EXTRACT(performance_metrics, "$.peak_memory")) as peak_memory
            ')
            ->first();
    }

    protected function capturePerformanceMetrics()
    {
        return json_encode([
            'response_time' => microtime(true) . ' - LARAVEL_START',
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
        ]);
    }
}