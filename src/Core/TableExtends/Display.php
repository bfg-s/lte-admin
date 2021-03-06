<?php

namespace LteAdmin\Core\TableExtends;

use Illuminate\Database\Eloquent\Model;
use Lar\Layout\Tags\TD;
use Lar\Layout\Tags\TH;
use Lar\Layout\Tags\TR;

class Display
{
    /**
     * @param $value
     * @param  array  $props
     * @param  Model|null  $model
     * @param  null  $field
     * @param  null  $title
     * @param  TD|null  $td
     * @param  TH|null  $th
     * @param  TR|null  $tr
     * @return mixed
     */
    public function hide_on_mobile(
        $value,
        array $props = [],
        Model $model = null,
        $field = null,
        $title = null,
        TD $td = null,
        TH $th = null,
        TR $tr = null
    ) {
        if ($td) {
            $td->addClass('d-none', 'd-sm-table-cell');
        }
        if ($th) {
            $th->addClass('d-none', 'd-sm-table-cell');
        }

        return $value;
    }
}
