<?php

namespace Lar\LteAdmin\Components;

use Lar\Layout\Tags\DIV;
use Lar\LteAdmin\Components\Traits\BuildHelperTrait;
use Lar\LteAdmin\Components\Traits\FieldMassControlTrait;
use Lar\LteAdmin\Core\Traits\Macroable;
use Lar\Tagable\Events\onRender;

/**
 * @methods Lar\LteAdmin\Components\FieldComponent::$inputs (string $name, string $label = null, ...$params)
 * @mixin ModalComponentMacroList
 * @mixin ModalComponentMethods
 */
class ModalComponent extends DIV implements onRender
{
    use FieldMassControlTrait, Macroable, BuildHelperTrait;

    /**
     * @var bool
     */
    protected $temporary = false;

    /**
     * @var string|null
     */
    protected $title;

    /**
     * @var mixed
     */
    protected $body;

    /**
     * @var array
     */
    protected $footer_buttons = [];

    /**
     * @var array
     */
    protected $left_footer_buttons = [];

    /**
     * @var array
     */
    protected $center_footer_buttons = [];

    /**
     * Modal constructor.
     * @param  \Closure|array|null  $content
     * @param  mixed  ...$params
     * @throws \ReflectionException
     */
    public function __construct($content = null, ...$params)
    {
        parent::__construct();

        $this->when($params);

        $this->addClass('modal-content');

        $this->body = new ModalBodyComponent($this);

        if ($content) {
            embedded_call($content, [
                ModalBodyComponent::class => $this->body,
                static::class => $this,
            ]);
        }

        $this->callConstructEvents();
    }

    /**
     * @param  string  $text
     * @return $this
     */
    public function title(string $text)
    {
        $this->title = $text;

        return $this;
    }

    /**
     * @return $this
     */
    public function temporary()
    {
        $this->temporary = true;

        return $this;
    }

    /**
     * @param  string  $text
     * @param  mixed  ...$params
     * @return ModalFooterButtonComponent
     */
    public function btn(string $text = '', ...$params)
    {
        $btn = new ModalFooterButtonComponent($text, ...$params);

        $this->footer_buttons[] = $btn;

        return $btn;
    }

    /**
     * @param  string  $text
     * @param  mixed  ...$params
     * @return ModalFooterButtonComponent
     */
    public function left_btn(string $text = '', ...$params)
    {
        $btn = new ModalFooterButtonComponent($text, ...$params);

        $this->left_footer_buttons[] = $btn;

        return $btn;
    }

    /**
     * @param  string  $text
     * @param  mixed  ...$params
     * @return ModalFooterButtonComponent
     */
    public function center_btn(string $text = '', ...$params)
    {
        $btn = new ModalFooterButtonComponent($text, ...$params);

        $this->center_footer_buttons[] = $btn;

        return $btn;
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool|FormComponent|\Lar\Tagable\Tag|mixed|string
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

        if ($this->temporary) {
            $this->attr('data-temporary');
        }

        $this->div(['modal-header'])->when(function (DIV $div) {
            if ($this->title) {
                $div->h5(['modal-title'])->text($this->title);
            }
            $div->a(['refresh_modal', 'href' => 'javascript:void(0)'])
                ->span()->text('⟳');
            $div->a(['close', 'style' => 'margin-left: 8px; padding-left: 0', 'href' => 'javascript:void(0)'])
                ->span(['aria-hidden' => 'true'])->text('&times;');
        });

        $this->appEnd($this->body);

        if (count($this->footer_buttons)) {
            $footer = $this->div(['modal-footer']);
            $row = $footer->row();
            $col_l = $row->div(['col-auto'])->textLeft();
            $col_c = $row->div(['col-auto'])->textCenter();
            $col_r = $row->div(['col-auto'])->textRight();
            foreach ($this->left_footer_buttons as $footer_button) {
                $col_l->appEnd($footer_button);
            }
            foreach ($this->center_footer_buttons as $footer_button) {
                $col_c->appEnd($footer_button);
            }
            foreach ($this->footer_buttons as $footer_button) {
                $col_r->appEnd($footer_button);
            }
        }
    }
}
