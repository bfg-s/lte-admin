<?php

namespace LteAdmin\Traits;

use Illuminate\Database\Eloquent\Model;

trait SearchFormConditionRulesTrait
{
    /**
     * @param  Model  $model
     * @return Model
     */
    public function makeModel($model)
    {
        if (request()->has('q')) {
            $r = request('q');
            if (is_string($r)) {
                if ($this->global_search_fields) {
                    $i = 0;
                    foreach ($this->global_search_fields as $global_search_field) {
                        $find = collect($this->fields)->where('field_name', $global_search_field)->first();
                        if ($find && (!isset($find['method']) || !is_embedded_call($find['method']))) {
                            if ($i) {
                                $model = $model->orWhere($global_search_field, 'like', "%{$r}%");
                            } else {
                                $model = $model->where($global_search_field, 'like', "%{$r}%");
                            }
                            $i++;
                        }
                    }
                } else {
                    $model = $model->orWhere(function ($q) use ($r) {
                        foreach ($this->fields as $field) {
                            if (!str_ends_with($field['field_name'], '_at')) {
                                $q = $q->orWhere($field['field_name'], 'like', "%{$r}%");
                            }
                        }
                        return $q;
                    });
                }
            } elseif (is_array($r)) {
                foreach ($r as $key => $val) {
                    if ($val != null) {
                        foreach ($this->fields as $field) {
                            if ($field['field_name'] === $key) {
                                $val = method_exists($field['class'], 'transformValue') ?
                                    $field['class']::transformValue($val) :
                                    $val;

                                if (is_embedded_call($field['method'])) {
                                    $result = call_user_func($field['method'], $model, $val, $key);

                                    if ($result) {
                                        $model = $result;
                                    }
                                } else {
                                    $model = $this->{$field['method']}(
                                        $model,
                                        $val,
                                        $key
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        return $model;
    }

    /**
     * @param  Model  $model
     * @param $key
     * @param $value
     * @return Model
     */
    protected function equally($model, $value, $key)
    {
        return $model->where($key, '=', $value);
    }

    /**
     * @param  Model  $model
     * @param $key
     * @param $value
     * @return Model
     */
    protected function not_equal($model, $value, $key)
    {
        return $model->where($key, '!=', $value);
    }

    /**
     * @param  Model  $model
     * @param $key
     * @param $value
     * @return Model
     */
    protected function more_or_equal($model, $value, $key)
    {
        return $model->where($key, '>=', $value);
    }

    /**
     * @param  Model  $model
     * @param $key
     * @param $value
     * @return Model
     */
    protected function less_or_equal($model, $value, $key)
    {
        return $model->where($key, '<=', $value);
    }

    /**
     * @param  Model  $model
     * @param $key
     * @param $value
     * @return Model
     */
    protected function more($model, $value, $key)
    {
        return $model->where($key, '>', $value);
    }

    /**
     * @param  Model  $model
     * @param $key
     * @param $value
     * @return Model
     */
    protected function less($model, $value, $key)
    {
        return $model->where($key, '<', $value);
    }

    /**
     * @param  Model  $model
     * @param $key
     * @param $value
     * @return Model
     */
    protected function like_right($model, $value, $key)
    {
        return $model->where($key, 'like', '%'.$value);
    }

    /**
     * @param  Model  $model
     * @param $key
     * @param $value
     * @return Model
     */
    protected function like_left($model, $value, $key)
    {
        return $model->where($key, 'like', $value.'%');
    }

    /**
     * @param  Model  $model
     * @param $key
     * @param $value
     * @return Model
     */
    protected function like_any($model, $value, $key)
    {
        return $model->where($key, 'like', '%'.$value.'%');
    }

    /**
     * @param  Model  $model
     * @param $key
     * @param $value
     * @return Model
     */
    protected function nullable($model, $value, $key)
    {
        if ($value) {
            return $model->whereNull($key);
        }

        return $model;
    }

    /**
     * @param  Model  $model
     * @param $key
     * @param $value
     * @return Model
     */
    protected function not_nullable($model, $value, $key)
    {
        if ($value) {
            return $model->whereNotNull($key);
        }

        return $model;
    }

    /**
     * @param  Model  $model
     * @param $key
     * @param $value
     * @return Model
     */
    protected function where_in($model, $value, $key)
    {
        return $model->whereIn($key, $value);
    }

    /**
     * @param  Model  $model
     * @param $key
     * @param $value
     * @return Model
     */
    protected function where_not_in($model, $value, $key)
    {
        return $model->whereNotIn($key, $value);
    }

    /**
     * @param  Model  $model
     * @param $key
     * @param $value
     * @return Model
     */
    protected function where_between($model, $value, $key)
    {
        return $model->whereBetween($key, $value);
    }

    /**
     * @param  Model  $model
     * @param $key
     * @param $value
     * @return Model
     */
    protected function where_not_between($model, $value, $key)
    {
        return $model->whereNotBetween($key, $value);
    }
}
