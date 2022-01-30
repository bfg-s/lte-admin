<?php

namespace LteAdmin\Components\Fields;

class NumericField extends InputField
{
    /**
     * @var string
     */
    protected $icon = 'fas fa-hashtag';

    /**
     * @var string[]
     */
    protected $data = [
        'load' => 'mask',
        'load-params' => '9{0,}',
    ];
}
