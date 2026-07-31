<?php

namespace models\User;

use components\Admin\Nexus\Editor;
use components\Admin\Nexus\Editor\EditorBehavior;
use components\Admin\Nexus\Editor\EditorBehaviorAction;
use components\Admin\AdminUserEditor;
use components\forms\controls\Button;
use components\forms\controls\MultiSelect;
use components\forms\controls\PasswordField;
use components\forms\controls\TextField;
use components\forms\Form;
use components\layout\Accordion;
use components\layout\Column;
use components\layout\Layout;
use components\layout\Row;
use components\Message\Message;
use components\SaveError\SaveError;
use core\App;
use core\communication\body\DictionaryBody;
use core\database\sql\Model;
use core\locale\LexiconUnit;
use core\utils\Components;
use core\utils\Models;
use core\view\Container;
use core\view\View;
use models\Group\Group;

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

    public function addControls(Container $container, ?Model $model): ?View {
        if (!is_null($model) && !($model instanceof User)) {
            return new Message("Provided resource is not User");
        }

        if ($this->editor instanceof AdminUserEditor) {
            $row = new Row();

            $loginAsUser = new Button($this->tr('Login as user'));
            $loginAsUser->addDataAttribute('url', $this->editor->createLoginAsUserUrl($model));
            $loginAsUser->addJavascriptInit('admin_user_loginAsUser');

            $container->add(
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

        $container->add(new Accordion($this->tr('RouteChasm user profile'), $column));

        return null;
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        if (!($model instanceof User)) {
            return new Message('Provided model for UserEditor is not instance of User');
        }

        $fields = App::getInstance()
            ->getRequest()
            ->body(DictionaryBody::class)
            ->getFields();

        $password = $fields->getStrict(self::NAME_PASSWORD);
        if ($action === EditorBehaviorAction::CREATE) {
            // Implicit unique check for tag in the User::save() function
            $model->tag = $fields->getStrict(self::NAME_TAG);

            if (empty($password)) {
                return new SaveError(
                    self::NAME_PASSWORD,
                    $this->tr('Password must not be empty')
                );
            }

            if (strlen($password) < 8) {
                return new SaveError(
                    self::NAME_PASSWORD,
                    $this->tr('Password must be at least 8 characters long')
                );
            }

            $model->password = password_hash($password, PASSWORD_DEFAULT);
        } else {
            if (!empty($password)) {
                if (strlen($password) < 8) {
                    return new SaveError(
                        self::NAME_PASSWORD,
                        $this->tr('Password must be at least 8 characters long')
                    );
                }

                $model->password = password_hash($password, PASSWORD_DEFAULT);
            }
        }

        $model->username = $fields->getStrict(self::NAME_USERNAME);

        $error = $model->save();

        $groups = array_map(
            fn($x) => intval($x),
            MultiSelect::parse($fields->get(self::NAME_GROUPS, ''))
        );

        $model->assignIds($groups);

        return Components::nullifyDatabaseAction($error);
    }
}