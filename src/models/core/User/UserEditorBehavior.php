<?php

namespace models\core\User;

use components\core\Admin\Nexus\Editor;
use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Admin\Nexus\Editor\EditorBehaviorAction;
use components\core\Admin\User\AdminUserEditor;
use components\core\Message\Message;
use components\core\SaveError\SaveError;
use components\layout\Accordion\Accordion;
use components\layout\Column\Column;
use components\layout\Layout;
use components\layout\Row\Row;
use core\App;
use core\database\sql\Model;
use core\forms\controls\Button\Button;
use core\forms\controls\MultiSelect\MultiSelect;
use core\forms\controls\PasswordField\PasswordField;
use core\forms\controls\TextField;
use core\forms\Form;
use core\locale\LexiconUnit;
use core\utils\Components;
use core\utils\Models;
use core\view\View;
use models\core\Group\Group;

class UserEditorBehavior implements EditorBehavior {
    use LexiconUnit, Editor\GetEditor, Editor\SetEditor;

    public const LEXICON_GROUP = 'editor.user';

    public const NAME_TAG = 'tag';
    public const NAME_USERNAME = 'username';
    public const NAME_PASSWORD = 'password';
    public const NAME_GROUPS = 'groups';



    public function __construct() {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function initForm(Form $form, ?Model $model): ?View {
        return null;
    }

    public function addControls(Layout $layout, ?Model $model): ?View {
        if (!is_null($model) && !($model instanceof User)) {
            return new Message("Provided resource is not User");
        }

        if ($this->editor instanceof AdminUserEditor) {
            $row = new Row();

            $loginAsUser = new Button($this->tr('Login as user'));
            $loginAsUser->addDataAttribute('url', $this->editor->createLoginAsUserUrl($model));
            $loginAsUser->addJavascriptInit('admin_user_loginAsUser');

            $layout->add(
                $row->add($loginAsUser)
            );
        }

        /** @var User|null $model */

        $column = new Column();

        $tagField = new TextField(
            self::NAME_TAG,
            $this->tr('Tag'),
        );

        if (!is_null($model)) {
            $tagField->setValue($model->tag);
            $tagField->readonly();
        }

        $column
            ->add($tagField)
            ->add(new TextField(
                self::NAME_USERNAME,
                $this->tr('Username'),
                Models::getString($model, 'username')
            ))
            ->add(new PasswordField(
                self::NAME_PASSWORD,
                $this->tr('Password'),
                '',
                addVisibilityControl: true
            ));

        $groups = [];
        foreach (Group::all() as $group) {
            $groups[$group->id] = $group->name;
        }

        $userGroups = is_null($model)
            ? []
            : array_map(fn($x) => $x->id, $model->getGroups());

        $column->add(new MultiSelect(self::NAME_GROUPS, 'Groups', $groups, $userGroups));

        $layout->add(new Accordion($this->tr('RouteChasm user profile'), $column));

        return null;
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        if (!($model instanceof User)) {
            return new Message('Provided model for UserEditor is not instance of User');
        }

        $body = App::getInstance()
            ->getRequest()
            ->getBody();

        if ($action === EditorBehaviorAction::CREATE) {
            // Implicit unique check for tag in the User::save() function
            $model->tag = $body->getStrict(self::NAME_TAG);
        }

        $password = $body->get(self::NAME_PASSWORD, '');
        $len = strlen($password);
        if ($len !== 0) {
            if ($len < 8) {
                return new SaveError(
                    self::NAME_PASSWORD,
                    $this->tr('Password must be at least 8 characters long')
                );
            }

            $model->password = password_hash($password, PASSWORD_DEFAULT);
        } else {
            $model->password = '';
        }

        $model->username = $body->getStrict(self::NAME_USERNAME);

        $error = $model->save();

        $groups = array_map(
            fn($x) => intval($x),
            MultiSelect::parse($body->get(self::NAME_GROUPS, ''))
        );

        $model->assignIds($groups);

        return Components::nullifyDatabaseAction($error);
    }
}