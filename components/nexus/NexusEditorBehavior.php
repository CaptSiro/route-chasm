<?php

namespace components\nexus;

use components\forms\Form;
use components\layout\Accordion;
use components\layout\Column;
use core\database\sql\Model;
use core\view\Container;
use core\view\View;

abstract class NexusEditorBehavior {
    protected NexusEditor $editor;

    /**
     * @var array<NexusEditorBehavior>
     */
    protected array $children = [];
    
    
    
    public function addBehavior(NexusEditorBehavior $behavior): static {
        $this->children[] = $behavior;
        return $this;
    }
    
    public function initialize(NexusEditor $editor, Form $form, ?Model $model): ?View {
        if (!is_null($error = $this->onFormInitialization($editor, $form, $model))) {
            return $error;
        }

        foreach ($this->children as $child) {
            if (!is_null($error = $this->initialize($editor, $form, $model))) {
                return $error;
            }
        }

        return null;
    }

    public function generate(Container $container, ?Model $model): ?View {
        if (!is_null($error = $this->onFormGeneration($container, $model))) {
            return $error;
        }

        foreach ($this->children as $child) {
            $container->add(
                new Accordion($child->getTitle(), $column = new Column())
            );

            if (!is_null($error = $this->generate($column, $model))) {
                return $error;
            }
        }

        return null;
    }

    public function submit(Model $model, NexusEditorAction $action): ?View {
        if (!is_null($error = $this->onSubmit($model, $action))) {
            return $error;
        }

        foreach ($this->children as $child) {
            if (!is_null($error = $this->submit($model, $action))) {
                return $error;
            }
        }

        return null;
    }



    public abstract function getTitle(): string;

    /**
     * @param Form $form The form to configure
     * @param Model|null $model The updating model (null on creation)
     * @return ?View Return View to render on error, null on success
     */
    public function onFormInitialization(NexusEditor $editor, Form $form, ?Model $model): ?View {
        $this->editor = $editor;
        return null;
    }

    /**
     * @param Container $container
     * @param Model|null $model The updating model (null on creation)
     * @return ?View Return View to render on error, null on success
     */
    public abstract function onFormGeneration(Container $container, ?Model $model): ?View;

    /**
     * Called after the form is submitted and the model has been populated/saved.
     *
     * The request and response model may be accessed via App instance
     *
     * @param Model $model The newly created or updated model instance
     * @param NexusEditorAction $action
     * @return ?View Return View to render on error, null on success
     *
     * @see App::getInstance()
     * @see App::getRequest()
     * @see App::getResponse()
     */
    public abstract function onSubmit(Model $model, NexusEditorAction $action): ?View;
}