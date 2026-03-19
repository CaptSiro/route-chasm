<?php

namespace components\pages\External;

use components\core\Admin\Nexus\Editor;
use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Admin\Nexus\Editor\EditorBehaviorAction;
use components\core\Message\Message;
use components\layout\Accordion\Accordion;
use components\layout\Column\Column;
use components\layout\Layout;
use core\App;
use core\database\sql\Model;
use core\forms\Form;
use core\locale\LexiconUnit;
use core\ResourceLoader;
use core\view\View;
use models\core\Page\ExternalPage;
use models\core\Page\Page;
use RuntimeException;

class ExternalPageEditorBehavior implements EditorBehavior {
    use ResourceLoader, LexiconUnit;

    public const LEXICON_GROUP = 'editor.external-page';

    public const NAME_PROMPT = 'prompt';
    public const PROPERTY_WEBPAGE_HTML = 'html';
    public const PROPERTY_WEBPAGE_CSS = 'css';
    public const PROPERTY_WEBPAGE_JS = 'js';



    public function __construct(
        protected EditorBehavior $behavior,
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function setEditor(Editor $editor): void {
        $this->behavior->setEditor($editor);
    }

    public function initForm(Form $form, ?Model $model): ?View {
        return $this->behavior->initForm($form, $model);
    }

    public function addControls(Layout $layout, ?Model $model): ?View {
        $external = $model instanceof Page
            ? ExternalPage::fromPage($model)
            : null;

        $column = new Column();
        $ret = $this->behavior->addControls($column, $external);

        $layout->add(new Accordion($this->tr('External Page'), $column));
        return $ret;
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        $body = App::getInstance()
            ->getRequest()
            ->getBody();

        if (!($model instanceof Page)) {
            return new Message($this->tr('Provided model must be type of Page'));
        }

        return $this->behavior->onSubmit(ExternalPage::fromPage($model), $action);
    }
}