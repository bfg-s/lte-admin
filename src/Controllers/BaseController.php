<?php

namespace Lar\LteAdmin\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Lar\LteAdmin\Core\ModelSaver;
use Lar\LteAdmin\ExtendProvider;
use Lar\LteAdmin\Models\LteRole;

/**
 * @template CurrentModel
 */
abstract class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var ExtendProvider|null
     */
    public static $extension_affiliation;

    /**
     * @var CurrentModel
     */
    public static $model;

    /**
     * Save request to model.
     *
     * @param  array|null  $data
     * @return bool|void
     */
    public function requestToModel(array $data = null)
    {
        $save = $data ?? request()->all();

        foreach (static::$crypt_fields as $crypt_field) {
            if (array_key_exists($crypt_field, $save)) {
                if ($save[$crypt_field]) {
                    $save[$crypt_field] = bcrypt($save[$crypt_field]);
                } else {
                    unset($save[$crypt_field]);
                }
            }
        }

        return $this->model() ? ModelSaver::do($this->model(), $save, $this) : false;
    }

    /**
     * Get only exists model.
     *
     * @return \Illuminate\Database\Eloquent\Model|\Lar\LteAdmin\Getters\Menu|string|null
     */
    public function existsModel()
    {
        return $this->model() && $this->model()->exists ? $this->model() : null;
    }

    /**
     * Get menu model.
     *
     * @return CurrentModel|\App\Models\Product|\App\Models\User|\Illuminate\Database\Eloquent\Model|\Lar\LteAdmin\Getters\Menu|string|null
     */
    public function model()
    {
        return gets()->lte->menu->model;
    }

    /**
     * Get model primary.
     *
     * @return \Lar\LteAdmin\Getters\Menu|object|string|null
     */
    public function model_primary()
    {
        return gets()->lte->menu->model_primary;
    }

    /**
     * Get now menu.
     *
     * @return array|\Lar\LteAdmin\Getters\Menu|null
     */
    public function now()
    {
        return gets()->lte->menu->now;
    }

    /**
     * Get resource type.
     *
     * @return \Lar\LteAdmin\Getters\Menu|string|null
     */
    public function type()
    {
        return gets()->lte->menu->type;
    }

    /**
     * Check type for resource.
     *
     * @param  string  $type
     * @return bool
     */
    public function isType(string $type)
    {
        return $this->type() === $type;
    }

    /**
     * @param  null  $name
     * @param  null  $default
     * @return array|mixed
     */
    public function data($name = null, $default = null)
    {
        if (! $name) {
            return gets()->lte->menu->data;
        }

        return gets()->lte->menu->data($name, $default);
    }

    /**
     * @param  string  $method
     * @return bool
     */
    public function can(string $method)
    {
        return lte_controller_can(static::class, $method);
    }

    /**
     * Get the map of resource methods to ability names.
     *
     * @return array
     */
    protected function resourceMap()
    {
        return [
            'index' => 'Index',
            'show' => 'Show',
            'create' => 'Create',
            'store' => 'Store',
            'edit' => 'Edit',
            'update' => 'Update',
            'destroy' => 'Destroy',
        ];
    }

    /**
     * @param  string  $method
     * @param  array  $params
     * @return ModalController
     */
    public function new_modal(string $method, array $params = [])
    {
        return (new ModalController($params))->setHandle(static::class.'::'.$method);
    }

    /**
     * @return ExtendProvider|null
     */
    public static function extension_affiliation()
    {
        if (static::$extension_affiliation) {
            return static::$extension_affiliation;
        }

        $provider = 'ServiceProvider';

        $providers = \LteAdmin::extensionProviders();

        $iteration = 1;

        while (! empty($piece = body_namespace_element(static::class, $iteration))) {
            if (isset($providers["{$piece}\\{$provider}"])) {
                static::$extension_affiliation = \LteAdmin::getExtension($providers["{$piece}\\{$provider}"]);

                break;
            }

            $iteration++;
        }

        return static::$extension_affiliation;
    }

    /**
     * @param  string  $method
     * @param  array|string[]  $roles
     * @param  string|null  $description
     * @return array
     */
    public static function generatePermission(string $method, array $roles = ['*'], string $description = null)
    {
        $provider = static::extension_affiliation();

        $p_desc = '';

        if ($provider && $provider::$description) {
            $p_desc = $provider::$description;
        }

        if (! $p_desc) {
            $p_desc = static::class;
        }

        return [
            'slug' => $method,
            'class' => static::class,
            'description' => $p_desc.($description ? " [$description]" : (\Lang::has("lte.about_method.{$method}") ? " [@lte.about_method.{$method}]" : " [{$method}]")),
            'roles' => $roles === ['*'] ? LteRole::all()->pluck('id')->toArray() : collect($roles)->map(static function ($item) {
                return is_numeric($item) ? $item : LteRole::where('slug', $item)->first()->id;
            })->filter()->values()->toArray(),
        ];
    }
}
