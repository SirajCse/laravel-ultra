<?php
// src/Table/Columns/SmartColumnFactory.php

namespace LaravelUltra\Table\Columns;

use LaravelUltra\AI\ColumnOptimizer;

class SmartColumnFactory
{
    protected $aiOptimizer;

    public function __construct()
    {
        $this->aiOptimizer = new ColumnOptimizer();
    }

    public function createSmartColumn($dataSample, $columnKey, $label = null)
    {
        // AI determines the best column type
        $suggestedType = $this->aiOptimizer->suggestColumnType($dataSample, $columnKey);

        $column = self::create($suggestedType, $columnKey, $label);

        // AI suggests additional configurations
        $config = $this->aiOptimizer->suggestColumnConfig($dataSample, $columnKey);
        $column->applyAIConfig($config);

        return $column;
    }

    public static function create($type, $key, $label = null)
    {
        return match($type) {
            // Basic types
            'text' => new TextColumn($key, $label),
            'number' => new NumberColumn($key, $label),
            'email' => new EmailColumn($key, $label),
            'url' => new UrlColumn($key, $label),

            // Advanced visualization
            'sparkline' => new SparklineColumn($key, $label),
            'heatmap' => new HeatmapColumn($key, $label),
            'trend' => new TrendColumn($key, $label),
            'gauge' => new GaugeColumn($key, $label),
            'radar' => new RadarColumn($key, $label),

            // Interactive
            'rating' => new RatingColumn($key, $label),
            'vote' => new VoteColumn($key, $label),
            'progress' => new ProgressColumn($key, $label),

            // Media
            'avatar' => new AvatarColumn($key, $label),
            'gallery' => new GalleryColumn($key, $label),
            'video' => new VideoColumn($key, $label),
            'audio' => new AudioColumn($key, $label),

            // Geographic
            'map' => new MapColumn($key, $label),
            'location' => new LocationColumn($key, $label),

            // Special
            'qr_code' => new QrCodeColumn($key, $label),
            'signature' => new SignatureColumn($key, $label),
            'color_picker' => new ColorPickerColumn($key, $label),

            default => new TextColumn($key, $label)
        };
    }
}