<?php

namespace LaravelUltra\Table\Columns;

class DateColumn extends Column
{
    protected $format = 'Y-m-d';

    public function format($format)
    {
        $this->format = $format;
        return $this;
    }

    protected function getType()
    {
        return 'date';
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'format' => $this->format,
        ]);
    }
}