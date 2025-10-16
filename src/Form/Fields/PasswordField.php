<?php

namespace LaravelUltra\Form\Fields;

class PasswordField extends Field
{
    protected function getType()
    {
        return 'password';
    }
}