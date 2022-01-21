<?php

namespace Lar\LteAdmin\Getters;

use Lar\Developer\Getter;

class Role extends Getter
{
    /**
     * @var string
     */
    public static $name = 'lte.role';

    public static function functions()
    {
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function default()
    {
        return collect([]);
    }
}
