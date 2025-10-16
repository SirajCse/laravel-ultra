<?php

namespace LaravelUltra\AI;

use Illuminate\Support\Facades\Http;

class AIService
{
    protected $config;
    protected $enabled;

    public function __construct($config)
    {
        $this->config = $config;
        $this->enabled = $config['enabled'] ?? false;
    }

    public function suggestTableColumns($dataSample, $context = '')
    {
        if (!$this->enabled) return [];

        $prompt = "Analyze this data sample and suggest optimal table columns with types, sorting, and searchability:\n\n";
        $prompt .= "Data Sample: " . json_encode(array_slice($dataSample, 0, 5)) . "\n";
        $prompt .= "Context: $context\n\n";
        $prompt .= "Respond with JSON: {columns: [{key: '', label: '', type: '', sortable: bool, searchable: bool}]}";

        return $this->callAI($prompt);
    }

    public function optimizeQuery($query, $dataSize)
    {
        if (!$this->enabled) return $query;

        $prompt = "Optimize this database query for performance with $dataSize records:\n$query";
        return $this->callAI($prompt);
    }

    public function generateFormFromDescription($description)
    {
        if (!$this->enabled) return [];

        $prompt = "Generate form field configuration for: $description\n";
        $prompt .= "Respond with JSON: {fields: [{name: '', label: '', type: '', required: bool, validation: []}]}";

        return $this->callAI($prompt);
    }

    public function predictTrends($historicalData)
    {
        if (!$this->enabled) return [];

        $prompt = "Analyze this data and predict trends:\n" . json_encode($historicalData);
        return $this->callAI($prompt);
    }

    protected function callAI($prompt)
    {
        try {
            if ($this->config['provider'] === 'openai' && !empty($this->config['openai']['api_key'])) {
                return $this->callOpenAI($prompt);
            }

            // Fallback to local AI or other providers
            return $this->callLocalAI($prompt);
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function callOpenAI($prompt)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config['openai']['api_key'],
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->config['openai']['model'] ?? 'gpt-3.5-turbo',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'max_tokens' => 1000,
        ]);

        if ($response->successful()) {
            return json_decode($response->json()['choices'][0]['message']['content'], true) ?? [];
        }

        return [];
    }

    protected function callLocalAI($prompt)
    {
        // Implementation for local AI models (Ollama, etc.)
        return [];
    }
}