<?php

namespace models\core\Language;

use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Admin\Nexus\Editor\EditorBehaviorAction;
use components\core\Admin\Nexus\Editor\GetEditor;
use components\core\Admin\Nexus\Editor\SetEditor;
use components\core\Message\Message;
use components\layout\Layout;
use core\App;
use core\database\sql\Model;
use core\forms\controls\Select\Select;
use core\forms\Form;
use core\locale\Locale;
use core\utils\Components;
use core\view\View;

class LanguageEditorBehavior implements EditorBehavior {
    use GetEditor, SetEditor;

    public const NAME_CODE = 'code';



    // EditorBehavior
    public function initForm(Form $form, ?Model $model): ?View {
        return null;
    }

    public function addControls(Layout $layout, ?Model $model): ?View {
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

        $layout->add(new Select(self::NAME_CODE, "Locale", $values));
        return null;
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        if ($action === EditorBehaviorAction::UPDATE) {
            return new Message('Provided model for UserEditor is not instance of User');
        }

        $body = App::getInstance()
            ->getRequest()
            ->getBody();

        /** @var Language $model */
        $model->code = $body->getStrict(self::NAME_CODE);

        return Components::nullifyDatabaseAction($model->save());
    }
}