<?php

namespace models\core\Group;

use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Admin\Nexus\Editor\EditorBehaviorAction;
use components\core\Admin\Nexus\Editor\GetEditor;
use components\core\Admin\Nexus\Editor\SetEditor;
use components\core\Admin\PrivilegeResourceMap\PrivilegeResourceMap;
use components\core\SaveError\SaveError;
use components\layout\Layout;
use core\App;
use core\database\sql\Model;
use core\forms\controls\TextField;
use core\forms\Form;
use core\locale\LexiconUnit;
use core\utils\Models;
use core\view\View;
use models\core\Privilege\Privilege;
use models\core\UserResource;

class GroupBehavior implements EditorBehavior {
    use GetEditor, SetEditor, LexiconUnit;

    public const LEXICON_GROUP = 'admin.group.editor';
    public const NAME_NAME = 'name';
    public const NAME_MAPPINGS = 'prm';



    public function __construct() {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function initForm(Form $form, ?Model $model): ?View {
        return null;
    }

    public function addControls(Layout $layout, ?Model $model): ?View {
        if (is_null($model)) {
            $layout->add(new TextField(self::NAME_NAME, 'Name'));
        } else {
            $layout->add($name = new TextField(
                self::NAME_NAME,
                'Name',
                Models::getString($model, 'name')
            ));

            if (!$model->isEditable()) {
                $name->readonly();
            }
        }

        $privileges = Privilege::all();
        $resources = UserResource::allOfType(UserResource::TYPE_SYSTEM);
        $map = [];

        if (!is_null($model)) {
            /** @var Group $group */
            $group = $model;

            foreach ($group->getMappings() as $mapping) {
                $position = PrivilegeResourceMap::createPositionRaw(
                    $mapping[Group::MAPPING_PRIVILEGE],
                    $mapping[Group::MAPPING_RESOURCE]
                );

                $map[$position] = true;
            }
        }

        $layout->add(
            new PrivilegeResourceMap($privileges, $resources, $map, self::NAME_MAPPINGS)
        );

        return null;
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        if (!($model instanceof Group)) {
            return new SaveError('', $this->tr('Provided model is not type of '. Group::class));
        }

        $request = App::getInstance()
            ->getRequest();
        $body = $request->getBody();

        if ($action === EditorBehaviorAction::CREATE || $model->isEditable()) {
            $model->set($body->toArray());
        }

        $model->save();

        $map = PrivilegeResourceMap::extractMap($body->toArray(), self::NAME_MAPPINGS);
        $mappings = [];

        foreach ($map as $position => $isset) {
            if (!$isset) {
                continue;
            }

            $ids = explode('-', $position);
            if (count($ids) < 2) {
                continue;
            }

            [$privilegeId, $resourceId] = $ids;
            $mappings[] = [
                Group::MAPPING_PRIVILEGE => $privilegeId,
                Group::MAPPING_RESOURCE => $resourceId
            ];
        }

        $model->clearMappings();
        $model->addMappingsRaw($mappings);
        return null;
    }
}