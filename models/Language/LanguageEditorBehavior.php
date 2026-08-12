<?php

namespace models\Language;

use components\forms\controls\Select;
use components\Message\Message;
use components\nexus\NexusEditorAction;
use components\nexus\NexusEditorBehavior;
use core\App;
use core\communication\body\DictionaryBody;
use core\database\sql\Model;
use core\locale\LexiconUnit;
use core\locale\Locale;
use core\utils\Components;
use core\view\Container;
use core\view\View;

class LanguageEditorBehavior extends NexusEditorBehavior {
    use LexiconUnit;

    public const LEXICON_GROUP = 'language.editor';

    public const NAME_CODE = 'code';



    public function __construct() {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function getTitle(): string {
        return $this->tr('Language');
    }

    public function onFormGeneration(Container $container, ?Model $model): ?View {
        if (!is_null($model)) {
            return new Message("Languages are not editable");
        }

        $languages = Language::all();
        $locales = App::getInstance()->getLocales();
        $available = array_diff($locales, $languages);

        if (empty($available)) {
            return new Message("There are no available locales left");
        }

        $values = [];
        foreach ($available as $locale) {
            /** @var Locale $locale */
            $values[$locale->getIdentifier()] = $locale->getName();
        }

        $container->add(new Select(self::NAME_CODE, "Locale", $values));
        return null;
    }

    public function onSubmit(Model $model, NexusEditorAction $action): ?View {
        if ($action === NexusEditorAction::UPDATE) {
            return new Message('Provided model for UserEditor is not instance of User');
        }

        $fields = App::getInstance()
            ->getRequest()
            ->body(DictionaryBody::class)
            ->getFields();

        /** @var Language $model */
        $model->code = $fields->getStrict(self::NAME_CODE);

        return Components::nullifyDatabaseAction($model->save());
    }
}