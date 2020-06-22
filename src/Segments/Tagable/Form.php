<?php

namespace Lar\LteAdmin\Segments\Tagable;

use Illuminate\Database\Eloquent\Model;
use Lar\Layout\Tags\INPUT;
use Lar\LteAdmin\Segments\Tagable\Traits\FieldMassControl;
use Lar\LteAdmin\Segments\Tagable\Traits\FormAutoMakeTrait;

/**
 * Class Col
 * @package Lar\LteAdmin\Segments\Tagable
 * @mixin \Lar\LteAdmin\Core\FormGroupComponents
 */
class Form extends \Lar\Layout\Tags\FORM {

    use FieldMassControl, FormAutoMakeTrait;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var string
     */
    protected $method = "post";

    /**
     * @var string|null
     */
    protected $action;

    /**
     * @var string
     */
    public static $last_id;

    /**
     * Form constructor.
     * @param  mixed  $model
     * @param  mixed  ...$params
     */
    public function __construct($model = null, ...$params)
    {
        if ($model instanceof \Closure) {

            $params[] = $model;

        } else {

            $this->model = $model;
        }

        if (!$this->model) {

            $this->model = gets()->lte->menu->model;
        }

        parent::__construct();

        $this->when($params);

        $this->toExecute('buildForm');

        $this->callConstructEvents();
    }

    /**
     * @param  string  $method
     * @return $this
     */
    public function method(string $method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @param  string  $action
     * @return $this
     */
    public function action(string $action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Form builder
     */
    protected function buildForm()
    {
        $this->callRenderEvents();

        $this->setMethod($this->method);

        $menu = gets()->lte->menu->now;

        $type = gets()->lte->menu->type;

        if (isset($menu['model.param'])) {

            $this->appEnd(INPUT::create(['type' => 'hidden', 'name' => '_after', 'value' => session('_after', 'index')])
                ->setDatas(['stated' => '_after']));
        }

        if (!$this->action && $type && $this->model && $menu) {

            $key = $this->model->getOriginal($this->model->getRouteKeyName());

            if ($type === 'edit' && isset($menu['link.update'])) {

                $this->action = $menu['link.update']($key);
                $this->hiddens(['_method' => 'PUT']);
            }
            else if ($type === 'create' && isset($menu['link.store'])) {

                $this->action = $menu['link.store'];
            }
        }

        else if (isset($menu['post']) && isset($menu['route']) && \Route::has($menu['route'] . '.post')) {

            $this->action = route($menu['route'] . '.post', $menu['route_params'] ?? []);
        }

        if (!$this->action) {

            $this->action = url()->current();
        }

        $this->setAction($this->action);

        $this->setEnctype('multipart/form-data');

        static::$last_id = $this->getUnique();

        $this->setId(static::$last_id);

        $this->attr('data-load', 'valid');
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool|Form|\Lar\Tagable\Tag|mixed|string
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if ($call = $this->call_group($name, $arguments)) {

            $call->setModel($this->model);

            return $call;
        }

        return parent::__call($name, $arguments);
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool|Field|FormGroup|mixed
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        if ($call = static::static_call_group($name, $arguments)) {

            return $call;
        }

        return parent::__callStatic($name, $arguments);
    }
}