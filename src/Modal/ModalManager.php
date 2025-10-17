<?php

namespace LaravelUltra\Modal;

use Illuminate\Support\Facades\Session;

class ModalManager
{
    protected $modals = [];
    protected $currentModal = null;

    public function open($modalKey, $props = [])
    {
        $this->currentModal = $modalKey;
        Session::put("ultra.modal.{$modalKey}", $props);

        return $this;
    }

    public function close($modalKey = null)
    {
        $modalKey = $modalKey ?? $this->currentModal;

        if ($modalKey) {
            Session::forget("ultra.modal.{$modalKey}");
        }

        $this->currentModal = null;
        return $this;
    }

    public function isOpen($modalKey)
    {
        return Session::has("ultra.modal.{$modalKey}");
    }

    public function getProps($modalKey)
    {
        return Session::get("ultra.modal.{$modalKey}", []);
    }

    public function reloadProps($modalKey, $newProps = [])
    {
        if ($this->isOpen($modalKey)) {
            $currentProps = $this->getProps($modalKey);
            Session::put("ultra.modal.{$modalKey}", array_merge($currentProps, $newProps));
        }

        return $this;
    }

    public function nested($parentModal, $childModal, $props = [])
    {
        $this->open($childModal, array_merge($props, [
            'parent_modal' => $parentModal,
        ]));

        return $this;
    }

    public function stacked($modals)
    {
        foreach ($modals as $modalKey => $props) {
            $this->open($modalKey, $props);
        }

        return $this;
    }
}