<?php

namespace LaravelUltra\Table\Columns;

class BooleanColumn extends Column
{
    protected $trueText = 'Yes';
    protected $falseText = 'No';

    public function labels($trueText, $falseText)
    {
        $this->trueText = $trueText;
        $this->falseText = $falseText;
        return $this;
    }

    protected function getType()
    {
        return 'boolean';
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'true_text' => $this->trueText,
            'false_text' => $this->falseText,
        ]);
    }
}