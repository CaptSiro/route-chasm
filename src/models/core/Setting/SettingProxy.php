<?php

namespace models\core\Setting;

use components\core\Admin\Nexus_v2\NexusProxy;

class SettingProxy extends NexusProxy {
    public function isItemEditable(): bool {
        /** @var Setting $item */
        $item = $this->item;
        return $item->editable;
    }

    protected function getEditValue(): string {
        if (!$this->isItemEditable()) {
            return '';
        }

        return parent::getEditValue();
    }

    protected function getDeleteValue(): string {
        if (!$this->isItemEditable()) {
            return '';
        }

        return parent::getDeleteValue();
    }
}