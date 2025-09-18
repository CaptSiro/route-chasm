<?php

namespace models\core\Language\Lexicon;

use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Admin\Nexus\Editor\EditorBehaviorAction;
use components\core\Admin\Nexus\Editor\SetEditor;
use components\core\Admin\Phrase\AdminPhraseEditor;
use components\core\Message\Message;
use components\layout\Column\Column;
use components\layout\Layout;
use components\layout\Tabs\Tabs;
use core\database\sql\Model;
use core\forms\controls\TextField;
use core\forms\Form;
use core\locale\LexiconUnit;
use core\utils\Models;
use core\view\StringRenderer;
use core\view\View;
use models\core\Language\Language;

class PhraseEditorBehavior implements EditorBehavior {
    use LexiconUnit, SetEditor;

    public const LEXICON_GROUP = 'admin.phrase.editor';

    public function __construct() {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function initForm(Form $form, ?Model $model): ?View {
        return null;
    }

    protected function addDynamicTranslationControls(array &$tabs, Phrase $phrase): void {
        /** @var array<int, Language> $languages */
        $languages = Models::identity(Language::all());

        foreach ($languages as $language) {
            $tabs[$language->getLocale()->getName()] = new Column();
        }

        foreach ($phrase->getTranslations() as $translation) {
            if (!isset($languages[$translation->languageId])) {
                continue;
            }

            $language = $languages[$translation->languageId];
            $tabs[$language->getLocale()->getName()]->add(
                Translation::createDynamicTranslationControl($translation->languageId, $translation)
            );
        }

        if ($this->editor instanceof AdminPhraseEditor) {
            foreach ($languages as $language) {
                $tabs[$language->getLocale()->getName()]->add(
                    new StringRenderer($this->editor->createAddTranslationButton($language->getId()))
                );
            }
        }
    }

    protected function addStaticTranslationControls(array &$tabs, Phrase $phrase): void {
        $translations = $phrase->getTranslations();

        foreach (Language::all() as $language) {
            $tabs[$language->getLocale()->getName()] = $column = new Column();
            $languageId = $language->getId();

            foreach ($translations as $translation) {
                if ($languageId === $translation->languageId) {
                    $column->add(Translation::createStaticTranslationControl(
                        $translation
                    ));

                    continue 2;
                }
            }

            $column->add(Translation::createStaticTranslationControl());
        }
    }

    public function addControls(Layout $layout, ?Model $model): ?View {
        if (is_null($model)) {
            return new Message($this->tr('Creating phrases is not supported'));
        }

        /** @var Phrase $model */
        $layout->add((new TextField('_ignored_', 'Default', $model->default))->readonly());

        $tabs = [];

        if ($model->isDynamic) {
            $this->addDynamicTranslationControls($tabs, $model);
        } else {
            $this->addStaticTranslationControls($tabs, $model);
        }

        $layout->add(new Tabs($tabs));
        return null;
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        return null;
    }
}