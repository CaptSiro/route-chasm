<?php

namespace components\forms\description;

use components\nexus\NexusEditorAction;
use components\nexus\NexusEditor;
use components\nexus\NexusEditorBehavior;
use core\App;
use core\communication\body\DictionaryBody;
use core\database\sql\Model;
use core\database\sql\ModelDescription;
use core\utils\Objects;
use core\view\Container;
use core\view\View;
use ReflectionClass;

class FormDescription extends NexusEditorBehavior {
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

                    if ($instance->isFirst()) {
                        $controls = [$property->getName() => $instance] + $controls;
                    } else {
                        $controls[$property->getName()] = $instance;
                    }

                    break;
                }
            }
        }

        return self::$descriptions[$class] = new FormDescription(
            Objects::getBaseClass($class),
            $controls
        );
    }

    public static function getEditor(string $class): NexusEditor {
        return new NexusEditor(
            ModelDescription::extract($class),
            static::extract($class)
        );
    }



    /**
     * @param array<string, ControlAttribute> $controls
     */
    public function __construct(
        protected string $title,
        protected array $controls
    ) {}



    /**
     * @return array<string, ControlAttribute>
     */
    public function getControls(): array {
        return $this->controls;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function onFormGeneration(Container $container, ?Model $model): ?View {
        $data = $model?->getData() ?? [];

        foreach ($this->controls as $property => $control) {
            $view = $control->getControl();

            if (isset($data[$property])) {
                $view->setValue($data[$property]);
            }

            $container->add($view);
        }

        return null;
    }

    public function onSubmit(Model $model, NexusEditorAction $action): ?View {
        $request = App::getInstance()->getRequest();
        $model->set($request
            ->body(DictionaryBody::class)
            ->getFields()
            ->toArray());

        $error = $model->save();

        if ($error instanceof View) {
            return $error;
        }

        return null;
    }
}