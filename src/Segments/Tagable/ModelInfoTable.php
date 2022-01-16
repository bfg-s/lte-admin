<?php

namespace Lar\LteAdmin\Segments\Tagable;

use Illuminate\Database\Eloquent\Model;
use Lar\Layout\Tags\DIV;
use Lar\Layout\Tags\SPAN;
use Lar\LteAdmin\Core\Traits\Delegable;
use Lar\LteAdmin\Core\Traits\Macroable;
use Lar\Developer\Core\Traits\Piplineble;

use function GraphQL\Validator\DocumentValidator;

/**
 * Class Col
 * @package Lar\LteAdmin\Segments\Tagable
 * @methods Lar\LteAdmin\Segments\Tagable\ModelTable::$extensions (...$params)
 * @mixin ModelInfoTableMacroList
 * @mixin ModelInfoTableMethods
 */
class ModelInfoTable extends DIV {

    use Macroable, Piplineble, Delegable;

    /**
     * @var bool
     */
    protected $only_content = true;

    /**
     * @var array|Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $rows = [];

    /**
     * @var string|null
     */
    protected $last;

    /**
     * ModelInfoTable constructor.
     * @param $model
     * @param  mixed  ...$params
     */
    public function __construct($model = null, ...$params)
    {
        if ($model instanceof Model || is_array($model)) {

            $this->model = $model;
        }

        else {

            $params[] = $model;
        }

        if (!$this->model) {

            $this->model = gets()->lte->menu->model;
        }

        if (!is_array($this->model)) {
            $this->model = static::fire_pipes($this->model, get_class($this->model));
        }

        parent::__construct();

        $this->when($params);

        $this->toExecute('buildTable');

        $this->callConstructEvents();
    }

    /**
     * @param  string|\Closure|array  $field
     * @param  string  $label
     * @return $this
     */
    public function row(string $label, $field)
    {
        $this->last = uniqid('row');

        $this->rows[$this->last] = ['field' => $field, 'label' => $label, 'info' => false, 'macros' => []];

        return $this;
    }

    /**
     * @param  string  $info
     * @return $this
     */
    public function info(string $info)
    {
        if ($this->last && isset($this->rows[$this->last])) {

            $this->rows[$this->last]['info'] = $info;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function id()
    {
        $this->row('lte.id', 'id');

        return $this;
    }

    /**
     * @return $this
     */
    public function created_at()
    {
        $this->row('lte.created_at', 'created_at')->true_data();

        return $this;
    }

    /**
     * @return $this
     */
    public function updated_at()
    {
        $this->row('lte.updated_at', 'updated_at')->true_data();

        return $this;
    }

    /**
     * @return $this
     */
    public function deleted_at()
    {
        $this->row('lte.deleted_at', 'deleted_at')->true_data();

        return $this;
    }

    /**
     * @return $this
     */
    public function at()
    {
        $this->updated_at()->created_at();

        return $this;
    }

    /**
     * @return $this
     */
    public function active_switcher()
    {
        $this->row('lte.active', 'active')->yes_no();

        return $this;
    }

    /**
     * Build table
     */
    protected function buildTable()
    {
        $this->callRenderEvents();

        $data = [];

        if ($this->model) {
            foreach ($this->rows as $row) {
                $field = $row['field'];
                $label = $row['label'];
                $macros = $row['macros'];
                if (is_string($field)) {
                    $field = multi_dot_call($this->model, $field);
                } else if (is_array($field) || is_embedded_call($field)) {
                    $field = embedded_call($field, [
                        is_object($this->model) ? get_class($this->model) : 'model' => $this->model,
                        ModelInfoTable::class => $this
                    ]);
                }
                foreach ($macros as $macro) {
                    $field = ModelTable::callExtension($macro[0], [
                        'model' => !is_array($this->model) ? $this->model : null,
                        'value' => $field,
                        'field' => $row['field'],
                        'title' => $row['label'],
                        'props' => $macro[1],
                        (is_object($this->model) ? get_class($this->model) : gettype($this->model)) => $this->model,
                    ]);
                }
                $label = __($label);
                if ($row['info']) {
                    $label .= SPAN::create(':space')->i(['title' => __($row['info'])])->icon_info_circle()->_render();
                }
                $data[] = [$label, $field];
            }
        }

        $this->table($data);
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this|bool|ModelInfoTable|\Lar\Tagable\Tag|string
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (ModelTable::hasExtension($name) && $this->last) {

            $this->rows[$this->last]['macros'][] = [$name, $arguments];

            return $this;
        }

        return parent::__call($name, $arguments);
    }
}
