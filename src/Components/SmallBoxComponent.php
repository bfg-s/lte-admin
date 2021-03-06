<?php

namespace LteAdmin\Components;

use Lar\Layout\Tags\DIV;
use LteAdmin\Traits\Delegable;
use LteAdmin\Traits\FontAwesome;
use LteAdmin\Traits\Macroable;
use LteAdmin\Traits\TypesTrait;

/**
 * @mixin SmallBoxComponentMacroList
 */
class SmallBoxComponent extends DIV
{
    use FontAwesome;
    use TypesTrait;
    use Macroable;
    use Delegable;

    /**
     * @var string[]
     */
    protected $props = [
        'info-box',
    ];

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $icon;

    /**
     * @var string|mixed
     */
    private $body;

    /**
     * @var array
     */
    private $params;

    /**
     * Alert constructor.
     * @param  string|null  $title
     * @param  string|null  $icon
     * @param $body
     * @param  mixed  ...$params
     */
    public function __construct(string $title = null, $body = '', string $icon = 'fas fa-info-circle', ...$params)
    {
        parent::__construct();

        $this->title = $title;

        $this->icon = $icon;

        $this->body = $body;

        $this->params = $params;

        $this->toExecute('_build');

        $this->callConstructEvents();
    }

    /**
     * @param  array  $title
     * @return $this
     */
    public function title($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param  string  $icon
     * @return $this
     */
    public function icon(string $icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @param  string|array  $body
     * @param  string  $small_info
     * @return $this
     */
    public function body($body, $small_info = '')
    {
        $this->body = [$body, $small_info];

        return $this;
    }

    /**
     * Build alert.
     */
    protected function _build()
    {
        $this->callRenderEvents();

        $this->span(['info-box-icon elevation-1'])
            ->addClass("bg-{$this->type}")
            ->i([$this->icon]);

        $content = $this->div(['info-box-content']);

        $content->span(['info-box-text'], $this->title);

        if (!is_array($this->body)) {
            $this->body = [$this->body];
        }

        $content->span(['info-box-number'], ($this->body[0] ?? ''))->small($this->body[1] ?? '');

        $content->when($this->params);
    }
}
