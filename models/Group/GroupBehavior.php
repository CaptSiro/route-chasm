<?php

namespace models\Group;

use components\Admin\PrivilegeResourceMap;
use components\forms\controls\TextField;
use components\nexus\NexusEditorAction;
use components\nexus\NexusEditorBehavior;
use components\SaveError\SaveError;
use core\App;
use core\communication\body\DictionaryBody;
use core\database\sql\Model;
use core\locale\LexiconUnit;
use core\utils\Models;
use core\view\Container;
use core\view\View;
use models\Privilege\Privilege;
use models\UserResource;

class GroupBehavior extends NexusEditorBehavior {
    use LexiconUnit;

    public const LEXICON_GROUP = 'admin.group.editor';
    public const NAME_NAME = 'name';
    public const NAME_MAPPINGS = 'prm';



    public function __construct() {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function getTitle(): string {
        return $this->tr('Group');
    }

    public function onFormGeneration(Container $container, ?Model $model): ?View {
        if (is_null($model)) {
            $container->add(new TextField(self::NAME_NAME, 'Name'));
        } else {
            $container->add($name = new TextField(
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

        $container->add(
            new PrivilegeResourceMap($privileges, $resources, $map, self::NAME_MAPPINGS)
        );

        return null;
    }

    public function onSubmit(Model $model, NexusEditorAction $action): ?View {
        if (!($model instanceof Group)) {
            return new SaveError('', $this->tr('Provided model is not type of '. Group::class));
        }

        $fields = App::getInstance()
            ->getRequest()
            ->body(DictionaryBody::class)
            ->getFields();

        if ($action === NexusEditorAction::CREATE || $model->isEditable()) {
            $model->set($fields->toArray());
        }

        $model->save();

        $map = PrivilegeResourceMap::extractMap($fields->toArray(), self::NAME_MAPPINGS);
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