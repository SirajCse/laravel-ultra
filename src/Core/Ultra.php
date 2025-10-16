<?php

namespace LaravelUltra\Core;

use LaravelUltra\Table\TableBuilder;
use LaravelUltra\Form\FormBuilder;
use LaravelUltra\Modal\ModalBuilder;
use LaravelUltra\AI\AIService;
use LaravelUltra\Realtime\RealtimeService;
use LaravelUltra\Analytics\AnalyticsService;
use LaravelUltra\Workflows\WorkflowBuilder;

class Ultra
{
    protected $app;
    protected $config;
    protected $aiService;
    protected $realtimeService;
    protected $analyticsService;

    public function __construct($app)
    {
        $this->app = $app;
        $this->config = $app['config']['ultra'] ?? [];

        // Initialize services
        $this->aiService = new AIService($this->config['ai'] ?? []);
        $this->realtimeService = new RealtimeService($this->config['realtime'] ?? []);
        $this->analyticsService = new AnalyticsService($this->config['analytics'] ?? []);
    }

    // Table System with AI enhancement
    public function table($source = null)
    {
        $builder = new TableBuilder($source, $this->config);

        // Auto-enable AI suggestions if configured
        if ($this->config['ai']['auto_enable'] ?? false) {
            $builder->withAISuggestions($this->aiService);
        }

        return $builder;
    }

    // Form System
    public function form($model = null, $data = [])
    {
        return new FormBuilder($model, $data, $this->config);
    }

    // Modal System
    public function modal($content = null)
    {
        return new ModalBuilder($content, $this->config);
    }

    // AI Service
    public function ai()
    {
        return $this->aiService;
    }

    // Real-time Service
    public function realtime()
    {
        return $this->realtimeService;
    }

    // Analytics Service
    public function analytics()
    {
        return $this->analyticsService;
    }

    // Workflow System
    public function workflow($name)
    {
        return new WorkflowBuilder($name);
    }

    // Component Generator
    public function generateComponent($type, $config = [])
    {
        return match($type) {
            'crud' => $this->generateCRUD($config),
            'dashboard' => $this->generateDashboard($config),
            'report' => $this->generateReport($config),
            default => throw new \Exception("Unknown component type: {$type}")
        };
    }

    protected function generateCRUD($config)
    {
        // AI-powered CRUD generation
        $model = $config['model'] ?? 'User';
        $fields = $this->aiService->generateFormFromDescription("{$model} CRUD form");

        return [
            'table' => $this->table($model)->withAISuggestions($this->aiService),
            'form' => $this->form($model)->withAIFields($fields),
            'modal' => $this->modal()->size('lg'),
        ];
    }

    // Static accessor
    public static function __callStatic($method, $parameters)
    {
        return app('ultra')->{$method}(...$parameters);
    }
}