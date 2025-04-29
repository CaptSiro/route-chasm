<?php

namespace models\core\Privilege;

use components\core\Admin\Nexus\NexusProxy;
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