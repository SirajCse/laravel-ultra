<?php

namespace LaravelUltra\Core;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \LaravelUltra\Table\TableBuilder table($source = null)
 * @method static \LaravelUltra\Form\FormBuilder form($model = null, $data = [])
 * @method static \LaravelUltra\Modal\ModalBuilder modal($content = null, $type = 'default')
 * @method static \LaravelUltra\AI\AIService ai()
 * @method static \LaravelUltra\Realtime\RealtimeService realtime()
 *
 * @see \LaravelUltra\Core\Ultra
 */
class UltraFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ultra';
    }
}