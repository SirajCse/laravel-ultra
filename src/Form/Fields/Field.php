<?php

namespace LaravelUltra\Form\Fields;

abstract class Field
{
    protected $name;
    protected $label;
    protected $required = false;
    protected $value;
    protected $rules = [];

    public function __construct($name, $label = null)
    {
        $this->name = $name;
        $this->label = $label ?? ucfirst(str_replace('_', ' ', $name));
    }

    public function required($required = true)
    {
        $this->required = $required;
        if ($required) {
            $this->rules[] = 'required';
        }
        return $this;
    }

    public function value($value)
    {
        $this->value = $value;
        return $this;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->getType(),
            'required' => $this->required,
            'value' => $this->value,
            'rules' => $this->rules,
        ];
    }

    abstract protected function getType();
}