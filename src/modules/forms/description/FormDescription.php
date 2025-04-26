<?php

namespace modules\forms\description;

use modules\forms\Form;
use modules\forms\FormSection;
use ReflectionClass;

class FormDescription implements FormSection {
    /**
     * @var array<string, static>
     */
    private static array $descriptions = [];

    public static function extract(string $class): FormDescription {
        if (isset(self::$descriptions[$class])) {
            return self::$descriptions[$class];
        }

        $reflection = new ReflectionClass($class);
        $controls = [];

        foreach ($reflection->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();

                if ($instance instanceof ControlAttribute) {
                    $instance->bindProperty($property);
                    $controls[$property->getName()] = $instance;
                    break;
                }
            }
        }

        return self::$descriptions[$class] = new FormDescription($controls);
    }



    /**
     * @param array<string, ControlAttribute> $controls
     */
    public function __construct(
        public readonly array $controls
    ) {}



    public function add(Form $form, array $data): void {
        foreach ($this->controls as $property => $control) {
            $view = $control->getControl();
            $view->setValue($data[$property] ?? null);
            $form->add($view);
        }
    }
}