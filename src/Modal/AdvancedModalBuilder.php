<?php

namespace LaravelUltra\Modal;

class AdvancedModalBuilder extends ModalBuilder
{
    protected $baseRoute;
    protected $eventBus;
    protected $nested = false;
    protected $stacked = false;
    protected $reloadProps = false;
    protected $lazyProps = [];
    protected $deferredProps = [];
    protected $loadWhenVisible = false;
    protected $local = false;
    protected $headless = false;

    public function baseRoute($route)
    {
        $this->baseRoute = $route;
        return $this;
    }

    public function eventBus($eventBus)
    {
        $this->eventBus = $eventBus;
        return $this;
    }

    public function nested($nested = true)
    {
        $this->nested = $nested;
        return $this;
    }

    public function stacked($stacked = true)
    {
        $this->stacked = $stacked;
        return $this;
    }

    public function reloadProps($reload = true)
    {
        $this->reloadProps = $reload;
        return $this;
    }

    public function lazyProps($props)
    {
        $this->lazyProps = $props;
        return $this;
    }

    public function deferredProps($props)
    {
        $this->deferredProps = $props;
        return $this;
    }

    public function loadWhenVisible($load = true)
    {
        $this->loadWhenVisible = $load;
        return $this;
    }

    public function local($local = true)
    {
        $this->local = $local;
        return $this;
    }

    public function headless($headless = true)
    {
        $this->headless = $headless;
        return $this;
    }

    public function closeModal($route = null)
    {
        $this->actions['close'] = [
            'type' => 'close',
            'route' => $route,
        ];
        return $this;
    }

    public function toResponse($request)
    {
        return array_merge(parent::toResponse($request), [
            'advanced' => [
                'base_route' => $this->baseRoute,
                'event_bus' => $this->eventBus,
                'nested' => $this->nested,
                'stacked' => $this->stacked,
                'reload_props' => $this->reloadProps,
                'lazy_props' => $this->lazyProps,
                'deferred_props' => $this->deferredProps,
                'load_when_visible' => $this->loadWhenVisible,
                'local' => $this->local,
                'headless' => $this->headless,
            ],
        ]);
    }
}