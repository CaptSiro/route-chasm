<?php

namespace models\extensions\Editable;

use core\database\sql\Column;

/**
 * @property bool $editable
 */
trait EditableExtension {
    #[Column('is_editable', Column::TYPE_BOOLEAN)]
    protected bool $editable;

    public function isEditable(): bool {
        return $this->editable;
    }

    public function setEditable(bool $editable): void {
        $this->editable = $editable;
    }
}