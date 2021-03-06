<?php

namespace LteAdmin\Traits\ModelTable;

use Closure;

trait TableExtensionTrait
{
    /**
     * @var array
     */
    public static $extensions = [];

    /**
     * Add extension class.
     * @param  string  $class
     */
    public static function addExtensionClass(string $class)
    {
        if (class_exists($class)) {
            $class = new $class();

            foreach (get_class_methods($class) as $method) {
                static::addExtension($method, $class);
            }
        }
    }

    /**
     * Add macro.
     * @param  string  $name
     * @param  Closure|object  $object
     * @param  string|null  $method
     */
    public static function addExtension(string $name, $object, string $method = null)
    {
        if (is_embedded_call($object)) {
            static::$extensions[$name] = $object;
        } else {
            if (!$method) {
                $method = $name;
            }
            static::$extensions[$name] = [$object, $method];
        }
    }

    /**
     * Call extension.
     * @param  string  $name
     * @param  array  $arguments
     * @return mixed|null
     */
    public static function callExtension(string $name, array $arguments)
    {
        if (static::hasExtension($name)) {
            return embedded_call(static::$extensions[$name], $arguments);
        }

        return null;
    }

    /**
     * Check has extension.
     * @param  string  $name
     * @return bool
     */
    public static function hasExtension(string $name)
    {
        return isset(static::$extensions[$name]);
    }

    /**
     * Call extension.
     * @param  string  $name
     * @param  array  $arguments
     * @return mixed|null
     */
    public static function callE(string $name, array $arguments)
    {
        if (static::hasExtension($name)) {
            return call_user_func_array(static::$extensions[$name], $arguments);
        }

        return null;
    }

    /**
     * Extension getter.
     * @return array
     */
    public static function getExtensionList()
    {
        return static::$extensions;
    }
}
