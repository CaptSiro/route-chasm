<?php

namespace core\fs;

use components\Admin\Nexus\Editor;
use components\Admin\Nexus\Editor\EditorBehavior;
use components\Admin\Nexus\Editor\EditorBehaviorAction;
use components\forms\Form;
use components\layout\Layout;
use components\Message\Message;
use core\database\sql\Model;
use core\locale\LexiconUnit;
use core\view\View;

class FileSystemEntryEditorBehavior implements EditorBehavior {
    use Editor\GetEditor, Editor\SetEditor, LexiconUnit;

    public const LEXICON_GROUP = 'file-system.editor';



    public function __construct() {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    protected function editingEntriesIsNotSupported(): View {
        return new Message(
            $this->tr('Creating/Editing entries is not supported')
        );
    }

    public function initForm(Form $form, ?Model $model): ?View {
        return $this->editingEntriesIsNotSupported();
    }

    public function addControls(Layout $layout, ?Model $model): ?View {
        return $this->editingEntriesIsNotSupported();
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        return $this->editingEntriesIsNotSupported();
    }
}