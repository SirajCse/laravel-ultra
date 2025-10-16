<?php

namespace LaravelUltra\Form;

use LaravelUltra\Form\Fields\FieldFactory;

class FormBuilder
{
    protected $model;
    protected $data;
    protected $fields = [];
    protected $config;

    public function __construct($model = null, $data = [], $config = [])
    {
        $this->model = $model;
        $this->data = $data;
        $this->config = $config;
    }

    public function addText($name, $label = null)
    {
        $this->fields[] = FieldFactory::text($name, $label);
        return $this;
    }

    public function addEmail($name, $label = null)
    {
        $this->fields[] = FieldFactory::email($name, $label);
        return $this;
    }

    public function addPassword($name, $label = null)
    {
        $this->fields[] = FieldFactory::password($name, $label);
        return $this;
    }

    public function required()
    {
        if (count($this->fields) > 0) {
            end($this->fields)->required();
        }
        return $this;
    }

    public function withRealTimeValidation()
    {
        // Enable real-time validation
        return $this;
    }

    public function toResponse($request)
    {
        return [
            'fields' => $this->getFieldsConfig(),
            'data' => $this->data,
            'model' => $this->model,
        ];
    }

    protected function getFieldsConfig()
    {
        return collect($this->fields)->map->toArray()->all();
    }
}