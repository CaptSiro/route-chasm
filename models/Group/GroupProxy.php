<?php

namespace models\Group;

use components\nexus\NexusProxy;
use models\extensions\Editable\EditableProxy;

class GroupProxy extends NexusProxy {
    use EditableProxy;

    protected function getDeleteValue(): string {
        if (!$this->isItemEditable()) {
            return '';
        }

        return parent::getDeleteValue();
    }

    protected function getEditValue(): string {
        $id = (string) $this->item->getId();

        return $this->createEditValue(
            $this->context->getUpdateUrl((string) $this->item->getId())
        );
    }
}