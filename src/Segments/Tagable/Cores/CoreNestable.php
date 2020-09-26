<?php

namespace Lar\LteAdmin\Segments\Tagable\Cores;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Lar\Layout\Abstracts\Component;
use Lar\Layout\Tags\DIV;
use Lar\Layout\Tags\LI;
use Lar\Layout\Tags\OL;
use Lar\LteAdmin\Segments\Tagable\ButtonGroup;

class CoreNestable extends DIV
{
    /**
     * @var string[]
     */
    protected $props = [
        'dd'
    ];

    /**
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\Relation|\Lar\LteAdmin\Getters\Menu|string|null
     */
    protected $model;

    /**
     * @var array|\Lar\LteAdmin\Getters\Menu|null
     */
    protected $menu;

    /**
     * @var string
     */
    protected $title_field = "title";

    /**
     * @var string
     */
    protected $parent_field = "parent_id";

    /**
     * Shoe default controls
     *
     * @var \Closure|array
     */
    protected $controls;

    /**
     * @var \Closure|array
     */
    protected $info_control;

    /**
     * @var \Closure|array
     */
    protected $delete_control;

    /**
     * @var \Closure|array
     */
    protected $edit_control;

    /**
     * @var string
     */
    private $order_by_field = "order";

    /**
     * @var string
     */
    private $order_by_type = "asc";

    /**
     * @var int
     */
    private $maxDepth = 5;

    /**
     * Col constructor.
     * @param  null  $model
     * @param  array|null  $instructions
     * @param  mixed  ...$params
     */
    public function __construct($model = null, array $instructions = [], ...$params)
    {
        $this->controls =
        $this->info_control =
        $this->delete_control =
        $this->edit_control = function () { return true; };



        $this->model = eloquent_instruction($model, $instructions);

        $this->menu = gets()->lte->menu->now;

        parent::__construct();

        $this->setDatas(['load' => 'nestable']);

        $this->when($params);
    }

    /**
     * @param  string|null  $field
     * @return $this
     */
    public function orderDesc(string $field = null)
    {
        $this->order_by_type = 'desc';

        if ($field) {

            $this->order_by_field = $field;
        }

        return $this;
    }

    /**
     * @param  string|null  $field
     * @param  string|null  $order
     * @return $this
     */
    public function orderBy(string $field = null, string $order = null)
    {
        if ($field) {

            $this->order_by_field = $field;
        }

        if ($order) {

            $this->order_by_type = $order;
        }

        return $this;
    }

    /**
     * Build nestable
     * @return $this
     */
    public function build()
    {
        $model = null;

        if ($this->model instanceof Relation) {

            $model = $this->model->getQuery()->getModel();
        }

        else if ($this->model instanceof Builder) {

            $model = $this->model->getModel();
        }

        else if ($this->model instanceof Model) {

            $model = $this->model;
        }

        if ($model) {

            if (array_search($this->parent_field, $model->getFillable()) === false) {

                $this->maxDepth = 1;
            }

            $this->setDatas(['model' => get_class($model)]);
        }

        else {

            $this->maxDepth = 1;
        }

        $this->setDatas(['max-depth' => $this->maxDepth, 'parent' => $this->parent_field]);

        $this->model = $this->model->orderBy($this->order_by_field, $this->order_by_type)->get();

        $this->makeList($this->maxDepth > 1 ? $this->model->whereNull($this->parent_field) : $this->model, $this);

        return $this;
    }

    /**
     * @param  Collection|Model[]  $model
     * @param  Component  $object
     */
    protected function makeList(Collection $model, Component $object)
    {
        $object->ol(['dd-list'])->when(function (OL $ol) use ($model) {

            foreach ($model as $item) {

                $this->makeItem($ol, $item);
            }
        });
    }

    /**
     * @param  Component  $object
     * @param  Model  $item
     */
    protected function makeItem(Component $object, Model $item)
    {
        $object->li(['dd-item dd3-item'])->setDatas(['id' => $item->id])->when(function (LI $li) use ($item) {
            $li->div(['dd-handle dd3-handle'])->when(function (DIV $div) use ($item) {
                $div->i(['class' => 'fas fa-arrows-alt']);
            });
            $li->div(['dd3-content'])->when(function (DIV $div) use ($item) {
                $div->span(['text'])->text(multi_dot_call($item, $this->title_field));
                if(($this->controls)($item)) {
                    $div->div(['float-right'])
                        ->appEndIf($this->menu, ButtonGroup::create(function (ButtonGroup $group) use ($item) {

                            $model = $item;
                            $key = $model->getRouteKey();

                            if (($this->edit_control)($item)) {
                                $group->resourceEdit($this->menu['link.edit']($key), '');
                            }

                            if (($this->delete_control)($item)) {
                                $group->resourceDestroy($this->menu['link.destroy']($key), '', $model->getRouteKeyName(), $key);
                            }

                            if (($this->info_control)($item)) {
                                $group->resourceInfo($this->menu['link.show']($key), '');
                            }
                    }));
                }
            });
            if ($this->maxDepth > 1) {
                $list = $this->model->where($this->parent_field, $item->id);
                if ($list->count()) {
                    /** @var Collection $list */
                    $this->makeList($list, $li);
                }
            }
        });
    }

    /**
     * @param  string  $field
     * @return $this
     */
    public function title_field(string $field)
    {
        $this->title_field = $field;

        return $this;
    }

    /**
     * @param  int  $depth
     * @return $this
     */
    public function maxDepth(int $depth)
    {
        $this->maxDepth = $depth;

        return $this;
    }

    /**
     * @param  \Closure|array|null  $test
     * @return $this
     */
    public function disableControls($test = null)
    {
        $this->controls = is_embedded_call($test) ? $test : function () { return false; };

        return $this;
    }

    /**
     * @param  \Closure|array|null  $test
     * @return $this
     */
    public function disableInfo($test = null)
    {
        $this->info_control = is_embedded_call($test) ? $test : function () { return false; };

        return $this;
    }

    /**
     * @param  \Closure|array|null  $test
     * @return $this
     */
    public function disableEdit($test = null)
    {
        $this->edit_control = is_embedded_call($test) ? $test : function () { return false; };

        return $this;
    }

    /**
     * @param  \Closure|array|null  $test
     * @return $this
     */
    public function disableDelete($test = null)
    {
        $this->delete_control = is_embedded_call($test) ? $test : function () { return false; };

        return $this;
    }
}