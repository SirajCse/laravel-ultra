<?php

namespace LaravelUltra\Table\Actions;

class EditAction extends Action
{
    protected $type = 'edit';
    protected $url;

    public function url($url)
    {
        $this->url = $url;
        return $this;
    }

    public function execute($data)
    {
        // Redirect to edit page or open modal
        if ($this->url) {
            return redirect()->to($this->url);
        }

        return $data;
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'url' => $this->url,
        ]);
    }
}