<?php

namespace core\fs;

use components\forms\Form;
use components\Message\Message;
use components\nexus\NexusEditorAction;
use components\nexus\NexusEditor;
use components\nexus\NexusEditorBehavior;
use core\database\sql\Model;
use core\locale\LexiconUnit;
use core\view\Container;
use core\view\View;

class FileSystemEntryEditorBehavior extends NexusEditorBehavior {
    use LexiconUnit;

    public const LEXICON_GROUP = 'file-system.editor';



    public function __construct() {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function getTitle(): string {
        return $this->tr('File System Entry Properties');
    }

    protected function editingEntriesIsNotSupported(): View {
        return new Message(
            $this->tr('Creating/Editing File System entries is not supported')
        );
    }

    public function onFormInitialization(NexusEditor $editor, Form $form, ?Model $model): ?View {
        return $this->editingEntriesIsNotSupported();
    }

    public function onFormGeneration(Container $container, ?Model $model): ?View {
        return $this->editingEntriesIsNotSupported();
    }

    public function onSubmit(Model $model, NexusEditorAction $action): ?View {
        return $this->editingEntriesIsNotSupported();
    }
}