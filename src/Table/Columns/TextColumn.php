<?php

namespace LaravelUltra\Table\Columns;

class TextColumn extends Column
{
    protected function getType()
    {
        return 'text';
    }
}