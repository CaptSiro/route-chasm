<?php

namespace models\core\User;

use components\core\Admin\Nexus\Editor;
use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Admin\Nexus\Editor\EditorBehaviorAction;
use components\core\Message\Message;
use components\core\SaveError\SaveError;
use components\layout\Accordion\Accordion;
use components\layout\Tabs\Tabs;
use core\App;
use core\database\sql\Model;
use core\forms\controls\MultiSelect\MultiSelect;
use core\forms\controls\PasswordField\PasswordField;
use core\forms\controls\TextField;
use core\forms\Form;
use core\forms\layout\Column\Column;
use core\view\View;
use models\core\Group\Group;

class UserEditorBehavior implements EditorBehavior {
    public const NAME_TAG = 'tag';
    public const NAME_USERNAME = 'username';
    public const NAME_PASSWORD = 'password';
    public const NAME_GROUPS = 'groups';

    public static function getEditor(): Editor {
        return new Editor\AdminNexusEditor(
            new static()
        );
    }



    public function initForm(Form $form, ?Model $model): void {
        if (!is_null($model) && !($model instanceof User)) {
            return;
        }

        /** @var User|null $model */

        $column = new Column();

        $tagField = new TextField(
            self::NAME_TAG,
            'Tag',
        );

        if (!is_null($model)) {
            $tagField->setValue($model->tag);
            $tagField->readonly();
        }

        $column
            ->add($tagField)
            ->add(new TextField(
                self::NAME_USERNAME,
                'Username',
                Model::getString($model, 'username')
            ))
            ->add(new PasswordField(
                self::NAME_PASSWORD,
                'Password',
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

        $form->add(new Accordion('RouteChasm user profile', $column));
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

            $password = $body->getStrict(self::NAME_PASSWORD);
            if (strlen($password) < 8) {
                return new SaveError(self::NAME_PASSWORD, 'Password must be at least 8 characters long');
            }

            $model->password = password_hash($password, PASSWORD_DEFAULT);
        }

        $model->username = $body->getStrict(self::NAME_USERNAME);
        $error = $model->save();

        $groups = array_map(
            fn($x) => intval($x),
            MultiSelect::parse($body->get(self::NAME_GROUPS, ''))
        );

        $model->assignIds($groups);

        if ($error instanceof View) {
            return $error;
        }

        return null;
    }
}