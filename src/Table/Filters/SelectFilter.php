<?php

namespace LaravelUltra\Table\Filters;

class SelectFilter extends Filter
{
    protected $options = [];
    protected $type = 'select';

    public function options($options)
    {
        $this->options = $options;
        return $this;
    }

    public function apply($query, $value)
    {
        if ($value) {
            $query->where($this->key, $value);
        }

        return $query;
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'options' => $this->options,
        ]);
    }
}