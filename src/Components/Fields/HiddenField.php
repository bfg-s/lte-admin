<?php

namespace LteAdmin\Components\Fields;

use Lar\Layout\Abstracts\Component;
use Lar\Layout\Tags\INPUT;
use LteAdmin\Components\FormGroupComponent;

class HiddenField extends FormGroupComponent
{
    /**
     * @var string
     */
    protected $type = 'hidden';

    /**
     * @var bool
     */
    protected $vertical = true;

    /**
     * @var null
     */
    protected $icon = null;

    /**
     * @var bool
     */
    protected $only_input = true;

    /**
     * @return Component|INPUT|mixed
     */
    public function field()
    {
        return INPUT::create([
            'type' => $this->type,
            'id' => $this->field_id,
            'name' => $this->name,
            'placeholder' => $this->title,
        ], ...$this->params)
            ->setValue($this->value);
    }

    /**
     * @return $this
     */
    public function disabled()
    {
        $this->params[] = ['disabled' => 'true'];

        return $this;
    }
}
