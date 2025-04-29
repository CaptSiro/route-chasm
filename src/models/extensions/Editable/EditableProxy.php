<?php

namespace models\extensions\Editable;

trait EditableProxy {
    public function isItemEditable(): bool {
        /** @var Editable $item */
        $item = $this->item;
        return $item->editable;
    }
}