<?php

namespace LaravelUltra\Table\Columns;

class ColumnFactory
{
    public static function text($key, $label = null)
    {
        return new TextColumn($key, $label);
    }

    public static function email($key, $label = null)
    {
        return new EmailColumn($key, $label);
    }

    public static function date($key, $label = null)
    {
        return new DateColumn($key, $label);
    }

    public static function number($key, $label = null)
    {
        return new NumberColumn($key, $label);
    }

    // Add this method for boolean columns
    public static function boolean($key, $label = null)
    {
        return new BooleanColumn($key, $label);
    }
}