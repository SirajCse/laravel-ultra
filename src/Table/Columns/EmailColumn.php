<?php

namespace LaravelUltra\Table\Columns;

class EmailColumn extends Column
{
    protected function getType()
    {
        return 'email';
    }
}