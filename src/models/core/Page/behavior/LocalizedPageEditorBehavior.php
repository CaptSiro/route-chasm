<?php

namespace models\core\Page\behavior;

use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Admin\Nexus\Editor\EditorBehaviorAction;
use components\core\Admin\Nexus\Editor\SetEditor;
use components\layout\Layout;
use core\database\sql\Model;
use core\forms\description\FormDescription;
use core\forms\Form;
use core\view\View;
use models\core\Page\PageLocalization;
use models\core\Page\PageMeta;

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