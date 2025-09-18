<?php

namespace components\core\Admin\Nexus\Editor;

use components\core\Admin\Nexus\Editor;
use components\layout\Layout;
use core\App;
use core\database\sql\Model;
use core\forms\Form;
use core\view\View;

interface EditorBehavior {
    public function setEditor(Editor $editor): void;

    /**
     * @param Form $form The form to configure
     * @param Model|null $model The updating model (null on creation)
     * @return ?View Return View to render on error, null on success
     */
    public function initForm(Form $form, ?Model $model): ?View;

    /**
     * @param Layout $layout
     * @param Model|null $model The updating model (null on creation)
     * @return ?View Return View to render on error, null on success
     */
    public function addControls(Layout $layout, ?Model $model): ?View;

    /**
     * Called after the form is submitted and the model has been populated/saved.
     *
     * The request and response model may be accessed via App instance
     *
     * @param Model $model The newly created or updated model instance
     * @param EditorBehaviorAction $action
     * @return ?View Return View to render on error, null on success
     *
     * @see App::getInstance()
     * @see App::getRequest()
     * @see App::getResponse()
     */
    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View;
}