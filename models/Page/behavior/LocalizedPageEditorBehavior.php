<?php

namespace models\Page\behavior;

use components\Admin\Nexus\Editor\EditorBehavior;
use components\Admin\Nexus\Editor\EditorBehaviorAction;
use components\Admin\Nexus\Editor\SetEditor;
use components\forms\description\FormDescription;
use components\forms\Form;
use components\layout\Layout;
use core\database\sql\Model;
use core\view\View;
use models\Page\PageLocalization;
use models\Page\PageMeta;

class LocalizedPageEditorBehavior implements EditorBehavior {
    use SetEditor;

    public function initForm(Form $form, ?Model $model): ?View {
        //todo
        //  - add PageMeta->initForm(...)
        return FormDescription::extract(PageLocalization::class)->initForm($form, $model);
    }

    public function addControls(Layout $layout, ?Model $model): ?View {
        /** @var ?PageLocalization $model */
        $error = FormDescription::extract(PageLocalization::class)->addControls($layout, $model);
        if (!is_null($error)) {
            return $error;
        }

        $layout->add(Form::title('Metadata'));

        $error = FormDescription::extract(PageMeta::class)->addControls($layout, is_null($model)
            ? null
            : PageMeta::fromLocalization($model)
        );
        if (!is_null($error)) {
            return $error;
        }

        return null;
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        //todo
        return null;
    }
}