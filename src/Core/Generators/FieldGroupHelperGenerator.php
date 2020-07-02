<?php

namespace Lar\LteAdmin\Core\Generators;

use Illuminate\Console\Command;
use Lar\Developer\Commands\Dump\DumpExecute;
use Lar\EntityCarrier\Core\Entities\ClassEntity;
use Lar\EntityCarrier\Core\Entities\DocumentorEntity;
use Lar\LteAdmin\Models\LteFunction;
use Lar\LteAdmin\Segments\Tagable\Field;
use Lar\LteAdmin\Segments\Tagable\Form;

/**
 * Class FormGroupHelperGenerator
 * @package Lar\LteAdmin\Core\Generators
 */
class FieldGroupHelperGenerator implements DumpExecute {

    /**
     * @param  Command  $command
     * @return mixed|string
     */
    public function handle(Command $command)
    {
        $namespace = namespace_entity("Lar\LteAdmin\Core");

        $namespace->class("FormGroupComponents", function (ClassEntity $class) {

            $class->doc(function (DocumentorEntity $doc) {

                $this->generateDefaultMethods($doc);
            });
        });

        return $namespace->render();
    }

    /**
     * Generate default methods
     *
     * @param DocumentorEntity $doc
     * @throws \ReflectionException
     */
    protected function generateDefaultMethods(DocumentorEntity $doc)
    {
        foreach (Field::$form_components as $name => $provider) {

            $doc->tagMethod('\\'.$provider, $name."(string \$name, string \$label = null, ...\$params)", "Make field for form ($name})");
        }

        foreach (Field::get_macro_names() as $macro_name) {

            $ref = Field::get_macro_reflex($macro_name);

            $doc->tagMethod(

                "\\".Field::class,
                $macro_name . "(".refl_params_entity($ref->getParameters()).")",
                "Field Macro $macro_name"
            );
        }

        foreach (Form::get_macro_names() as $macro_name) {

            $ref = Form::get_macro_reflex($macro_name);

            $doc->tagMethod(

                "\\".Form::class,
                $macro_name . "(".refl_params_entity($ref->getParameters()).")",
                "Form Macro $macro_name"
            );
        }
    }
}