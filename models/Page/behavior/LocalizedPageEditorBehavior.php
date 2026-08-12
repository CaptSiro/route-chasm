<?php

namespace models\Page\behavior;

use components\forms\description\FormDescription;
use components\forms\Form;
use components\nexus\NexusEditorAction;
use components\nexus\NexusEditor;
use components\nexus\NexusEditorBehavior;
use core\database\sql\Model;
use core\locale\LexiconUnit;
use core\view\Container;
use core\view\View;
use models\Page\PageLocalization;
use models\Page\PageMeta;

class LocalizedPageEditorBehavior extends NexusEditorBehavior {
    use LexiconUnit;



    public function getTitle(): string {
        return $this->tr('Localization');
    }

    public function onFormInitialization(NexusEditor $editor, Form $form, ?Model $model): ?View {
        return FormDescription::extract(PageLocalization::class)
            ->onFormInitialization($editor, $form, $model);
    }

    public function onFormGeneration(Container $container, ?Model $model): ?View {
        /** @var ?PageLocalization $model */
        $error = FormDescription::extract(PageLocalization::class)
            ->onFormGeneration($container, $model);
        if (!is_null($error)) {
            return $error;
        }

        $container->add(Form::title('Metadata'));

        $error = FormDescription::extract(PageMeta::class)
            ->onFormGeneration($container, is_null($model)
                ? null
                : PageMeta::fromLocalization($model)
        );

        if (!is_null($error)) {
            return $error;
        }

        return null;
    }

    public function onSubmit(Model $model, NexusEditorAction $action): ?View {
        //todo
        return null;
    }
}