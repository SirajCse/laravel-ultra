<?php

namespace LaravelUltra\Form\Fields;

class FieldFactory
{
    public static function text($name, $label = null)
    {
        return new TextField($name, $label);
    }

    public static function email($name, $label = null)
    {
        return new EmailField($name, $label);
    }

    public static function password($name, $label = null)
    {
        return new PasswordField($name, $label);
    }
}