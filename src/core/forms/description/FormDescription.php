<?php

namespace core\forms\description;

use components\core\Admin\Nexus\Editor;
use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Admin\Nexus\Editor\EditorBehaviorAction;
use components\layout\Layout;
use core\App;
use core\database\sql\Model;
use core\forms\Form;
use core\view\View;
use ReflectionClass;

class FormDescription implements EditorBehavior {
    use Editor\SetEditor;

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

        return self::$descriptions[$class] = new FormDescription($controls);
    }

    public static function getEditor(string $class): Editor {
        return new Editor\AdminNexusEditor(
            static::extract($class)
        );
    }



    /**
     * @param array<string, ControlAttribute> $controls
     */
    public function __construct(
        protected array $controls
    ) {}



    /**
     * @return array<string, ControlAttribute>
     */
    public function getControls(): array {
        return $this->controls;
    }

    public function initForm(Form $form, ?Model $model): ?View {
        return null;
    }

    public function addControls(Layout $layout, ?Model $model): ?View {
        $data = $model?->getData() ?? [];

        foreach ($this->controls as $property => $control) {
            $view = $control->getControl();

            if (isset($data[$property])) {
                $view->setValue($data[$property]);
            }

            $layout->add($view);
        }

        return null;
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        $request = App::getInstance()->getRequest();
        $model->set($request->getBody()->toArray());
        $error = $model->save();

        if ($error instanceof View) {
            return $error;
        }

        return null;
    }
}