<?php

namespace LaravelUltra\Table\Actions;

abstract class Action
{
    protected $name;
    protected $label;
    protected $type;
    protected $confirm = false;
    protected $confirmMessage = 'Are you sure?';

    public function __construct($name, $label = null)
    {
        $this->name = $name;
        $this->label = $label ?? ucfirst($name);
    }

    public function confirm($message = null)
    {
        $this->confirm = true;
        $this->confirmMessage = $message ?? $this->confirmMessage;
        return $this;
    }

    abstract public function execute($data);

    public function toArray()
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'confirm' => $this->confirm,
            'confirm_message' => $this->confirmMessage,
        ];
    }
}