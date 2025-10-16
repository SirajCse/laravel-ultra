<?php
// src/Core/Ultra.php

namespace LaravelUltra\Core;

use Illuminate\Support\Facades\App;
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
    protected $features = [];

    public function __construct($app)
    {
        $this->app = $app;
        $this->config = $app['config']['ultra'];
        $this->detectFeatures();
    }

    public function table($source = null)
    {
        $builder = new TableBuilder($source, $this->config);

        // Auto-enable AI if configured
        if ($this->config['ai']['auto_enable'] ?? true) {
            $builder->withAISuggestions();
        }

        return $builder;
    }

    public function form($model = null, $data = [])
    {
        $builder = new FormBuilder($model, $data, $this->config);

        if ($this->config['ai']['auto_enable'] ?? true) {
            $builder->withAIGeneration();
        }

        return $builder;
    }

    public function modal($content = null, $type = 'default')
    {
        return new ModalBuilder($content, $type, $this->config);
    }

    public function ai()
    {
        return App::make(AIService::class);
    }

    public function realtime()
    {
        return App::make(RealtimeService::class);
    }

    public function analytics()
    {
        return App::make(AnalyticsService::class);
    }

    public function workflow($name)
    {
        return new WorkflowBuilder($name, $this->config);
    }

    public function createComponent($type, $config = [])
    {
        return match($type) {
            'dashboard' => $this->createDashboard($config),
            'crud' => $this->createCRUD($config),
            'admin_panel' => $this->createAdminPanel($config),
            'report' => $this->createReport($config),
            default => throw new \Exception("Unknown component type: {$type}")
        };
    }

    // AI-Powered component generation
    public function generateWithAI($description)
    {
        return $this->ai()->generateComponent($description);
    }

    // Multi-tenant support
    public function forTenant($tenantId)
    {
        config()->set('ultra.tenant_id', $tenantId);
        return $this;
    }

    // Feature detection
    protected function detectFeatures()
    {
        $this->features = [
            'inertia' => class_exists('Inertia\Inertia'),
            'livewire' => class_exists('Livewire\Livewire'),
            'vue' => $this->config['frontend']['vue'] ?? true,
            'react' => $this->config['frontend']['react'] ?? true,
            'broadcasting' => $this->config['realtime']['enabled'] ?? false,
        ];
    }

    public function getDetectedFeatures()
    {
        return $this->features;
    }
}