<?php

namespace LteAdmin\Components\SearchFields;

use Carbon\Carbon;
use LteAdmin\Components\Fields\DateField;

class DateSearchField extends DateField
{
    /**
     * @var string
     */
    public static $condition = '>=';

    /**
     * @param $value
     * @return Carbon
     */
    public static function transformValue($value)
    {
        return Carbon::create($value);
    }
}
