<?php

namespace Lar\LteAdmin\Segments\Tagable\Fields;

/**
 * Class Autocomplete
 * @package Lar\LteAdmin\Segments\Tagable\Fields
 */
class Autocomplete extends Select
{
    /**
     * @var string
     */
    protected $icon = 'fas fa-tag';

    public function __construct(string $name, string $title = null, ...$params)
    {
        parent::__construct($name, $title, $params);

        $this->data['tags'] = 'true';
    }
}
