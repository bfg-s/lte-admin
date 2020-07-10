<?php

namespace Lar\LteAdmin\Segments\Tagable;

use Lar\Layout\Tags\DIV;
use Lar\LteAdmin\Core\Traits\Macroable;
use Lar\LteAdmin\Segments\Tagable\Traits\BuildHelperTrait;
use Lar\LteAdmin\Segments\Tagable\Traits\FieldMassControl;
use Lar\Tagable\Events\onRender;

/**
 * Class TabContent
 * @package Lar\LteAdmin\Segments\Tagable
 * @methods Lar\LteAdmin\Segments\Tagable\Field::$form_components (string $name, string $label = null, ...$params)
 * @mixin TabContentMacroList
 * @mixin TabContentMethods
 */
class TabContent extends DIV implements onRender {

    use FieldMassControl, Macroable, BuildHelperTrait;

    /**
     * @var string[]
     */
    protected $props = [
        'tab-pane p-3',
        'role' => 'tabpanel',
    ];

    /**
     * Row constructor.
     * @param  mixed  ...$params
     */
    public function __construct(...$params)
    {
        parent::__construct();

        $this->when($params);

        $this->callConstructEvents();
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool|Form|\Lar\Tagable\Tag|mixed|string
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if ($call = $this->call_group($name, $arguments)) {

            return $call;
        }

        return parent::__call($name, $arguments);
    }

    /**
     * @return mixed|void
     * @throws \ReflectionException
     */
    public function onRender()
    {
        $this->callRenderEvents();
    }
}