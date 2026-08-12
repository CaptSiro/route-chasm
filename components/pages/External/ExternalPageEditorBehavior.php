<?php

namespace components\pages\External;

use components\forms\Form;
use components\layout\Accordion;
use components\layout\Column;
use components\Message\Message;
use components\nexus\NexusEditorAction;
use components\nexus\NexusEditor;
use components\nexus\NexusEditorBehavior;
use core\database\sql\Model;
use core\locale\LexiconUnit;
use core\ResourceLoader;
use core\view\Container;
use core\view\View;
use models\Page\ExternalPage;
use models\Page\Page;

class ExternalPageEditorBehavior extends NexusEditorBehavior {
    use ResourceLoader, LexiconUnit;

    public const LEXICON_GROUP = 'editor.external-page';

    public const NAME_PROMPT = 'prompt';
    public const PROPERTY_WEBPAGE_HTML = 'html';
    public const PROPERTY_WEBPAGE_CSS = 'css';
    public const PROPERTY_WEBPAGE_JS = 'js';



    public function __construct(
        protected NexusEditorBehavior $behavior,
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function getTitle(): string {
        return $this->tr('External Page Properties');
    }

    public function onFormInitialization(NexusEditor $editor, Form $form, ?Model $model): ?View {
        return $this->behavior->onFormInitialization($editor, $form, $model);
    }

    public function onFormGeneration(Container $container, ?Model $model): ?View {
        $external = $model instanceof Page
            ? ExternalPage::fromPage($model)
            : null;

        $column = new Column();
        $ret = $this->behavior->onFormGeneration($column, $external);

        $container->add(new Accordion($this->tr('External Page'), $column));
        return $ret;
    }

    public function onSubmit(Model $model, NexusEditorAction $action): ?View {
        if (!($model instanceof Page)) {
            return new Message($this->tr('Provided model must be type of Page'));
        }

        return $this->behavior->onSubmit(ExternalPage::fromPage($model), $action);
    }
}