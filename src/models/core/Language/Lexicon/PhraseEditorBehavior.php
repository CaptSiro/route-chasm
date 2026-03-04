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
use core\App;
use core\database\sql\Model;
use core\forms\controls\HiddenField;
use core\forms\controls\TextField;
use core\forms\Form;
use core\locale\LexiconUnit;
use core\utils\Models;
use core\view\StringRenderer;
use core\view\View;
use models\core\Language\Language;

class PhraseEditorBehavior implements EditorBehavior {
    use LexiconUnit, SetEditor;

    public const LEXICON_GROUP = AdminPhraseEditor::LEXICON_GROUP;
    public const NAME_DELETED_TRANSLATIONS = 'deleted_translations';

    public function __construct() {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function initForm(Form $form, ?Model $model): ?View {
        $form->setBodyTransformer('form_json');
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
                Translation::createDynamicTranslationControl(
                    $translation->languageId,
                    $translation,
                    self::NAME_DELETED_TRANSLATIONS
                )
            );
        }

        if ($this->editor instanceof AdminPhraseEditor) {
            foreach ($languages as $language) {
                $tabs[$language->getLocale()->getName()]->add(
                    new StringRenderer($this->editor->createAddTranslationButton($language->id))
                );
            }
        }
    }

    protected function addStaticTranslationControls(array &$tabs, Phrase $phrase): void {
        $translations = $phrase->getTranslations();

        foreach (Language::all() as $language) {
            $tabs[$language->getLocale()->getName()] = $column = new Column();
            $languageId = $language->id;

            foreach ($translations as $translation) {
                if ($languageId === $translation->languageId) {
                    $column->add(Translation::createStaticTranslationControl(
                        $languageId,
                        $translation
                    ));

                    continue 2;
                }
            }

            $column->add(Translation::createStaticTranslationControl($languageId));
        }
    }

    public function addControls(Layout $layout, ?Model $model): ?View {
        if (is_null($model)) {
            return new Message($this->tr('Creating phrases is not supported'));
        }

        $layout->add(new HiddenField(self::NAME_DELETED_TRANSLATIONS));

        /** @var Phrase $model */
        $layout->add((new TextField('_ignored_', $this->tr('Default'), $model->default))
            ->readonly());
        $layout->add((new TextField('_ignored_', $this->tr('Group'), $model->getLexiconGroup()->name))
            ->readonly());

        $tabs = [];

        if ($model->isDynamic) {
            $this->addDynamicTranslationControls($tabs, $model);
        } else {
            $this->addStaticTranslationControls($tabs, $model);
        }

        $layout->add(new Tabs($tabs));
        return null;
    }

    protected function onSubmitStatic(Phrase $phrase, array $objects): ?View {
        $translations = Models::identity($phrase->getTranslations());

        foreach ($objects as $object) {
            if (empty($object[Translation::NAME_TRANSLATION])) {
                continue;
            }

            $translation = !empty($object[Translation::NAME_TRANSLATION_ID])
                ? ($translations[intval($object[Translation::NAME_TRANSLATION_ID])] ?? null)
                : new Translation();

            if (is_null($translation)) {
                continue;
            }

            $translation->set($object);
            $translation->setPhrase($phrase);
            $translation->save();
        }

        return null;
    }

    protected function onSubmitDynamic(Phrase $phrase, array $objects): ?View {
        $translations = Models::identity($phrase->getTranslations());

        foreach ($objects as $object) {
            if (empty($object[Translation::NAME_TRANSLATION]) && empty($object[Translation::NAME_TRANSLATION_ID])) {
                continue;
            }

            $translation = !empty($object[Translation::NAME_TRANSLATION_ID])
                ? ($translations[intval($object[Translation::NAME_TRANSLATION_ID])] ?? null)
                : new Translation();

            if (is_null($translation)) {
                continue;
            }

            if (empty($object[Translation::NAME_TRANSLATION]) && !is_null($translation->getId())) {
                $translation->delete();
                continue;
            }

            $translation->set($object);
            $translation->setPhrase($phrase);
            $translation->save();
        }

        return null;
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        /** @var Phrase $model */
        if ($action === EditorBehaviorAction::CREATE) {
            return new Message(
                $this->tr('Creating phrases is not supported')
            );
        }

        $request = App::getInstance()->getRequest();
        $body = $request->getBody();

        if (!empty($deleted = $body->get(self::NAME_DELETED_TRANSLATIONS))) {
            foreach (explode(',', $deleted) as $id) {
                Translation::fromId($id)?->delete();
            }
        }

        $objects = Models::transpose(
            $body->toArray(),
            Translation::getControlNames(),
            count($body->getStrict(Translation::NAME_TRANSLATION_ID))
        );

        return $this->submitTranslations($model, $objects);
    }

    public function submitTranslations(Phrase $phrase, array $translationRawObjects): ?View {
        if (!$phrase->isDynamic) {
            if (!is_null($error = $this->onSubmitStatic($phrase, $translationRawObjects))) {
                return $error;
            }
        } else {
            if (!is_null($error = $this->onSubmitDynamic($phrase, $translationRawObjects))) {
                return $error;
            }
        }

        return null;
    }
}