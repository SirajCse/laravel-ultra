<?php

namespace LaravelUltra\Table\Filters;

abstract class Filter
{
    protected $key;
    protected $label;
    protected $type;
    protected $value;

    public function __construct($key, $label = null)
    {
        $this->key = $key;
        $this->label = $label ?? ucfirst($key);
    }

    abstract public function apply($query, $value);

    public function toArray()
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type,
            'value' => $this->value,
        ];
    }
}