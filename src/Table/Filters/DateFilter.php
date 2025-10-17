<?php

namespace LaravelUltra\Table\Filters;

class DateFilter extends Filter
{
    protected $type = 'date';
    protected $format = 'Y-m-d';
    protected $range = false;

    public function range($range = true)
    {
        $this->range = $range;
        return $this;
    }

    public function format($format)
    {
        $this->format = $format;
        return $this;
    }

    public function apply($query, $value)
    {
        if (!$value) return $query;

        if ($this->range && is_array($value)) {
            if (!empty($value['from'])) {
                $query->whereDate($this->key, '>=', $value['from']);
            }
            if (!empty($value['to'])) {
                $query->whereDate($this->key, '<=', $value['to']);
            }
        } else {
            $query->whereDate($this->key, $value);
        }

        return $query;
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'range' => $this->range,
            'format' => $this->format,
        ]);
    }
}