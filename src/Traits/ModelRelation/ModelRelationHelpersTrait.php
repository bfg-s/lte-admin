<?php

namespace LteAdmin\Traits\ModelRelation;

trait ModelRelationHelpersTrait
{
    /**
     * @param  callable  $call
     * @return $this
     */
    public function empty(callable $call)
    {
        $this->on_empty = $call;

        return $this;
    }
}
