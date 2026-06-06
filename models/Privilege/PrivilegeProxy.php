<?php

namespace models\Privilege;

use components\Admin\Nexus\NexusProxy;
use models\extensions\Editable\EditableProxy;

class PrivilegeProxy extends NexusProxy {
    use EditableProxy;

    protected function getDeleteValue(): string {
        if (!$this->isItemEditable()) {
            return '';
        }

        return parent::getDeleteValue();
    }

    protected function getEditValue(): string {
        if (!$this->isItemEditable()) {
            return '';
        }

        return parent::getEditValue();
    }
}