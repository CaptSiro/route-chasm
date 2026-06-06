<?php

namespace models\extensions\Editable;

use core\database\sql\Column;

const PROPERTY_EDITABLE = 'editable';

trait EditableExtension {
    #[Column('is_editable', Column::TYPE_BOOLEAN)]
    public bool $editable = false;



    public function isEditable(): bool {
        return $this->editable;
    }

    public function isDeletable(): bool {
        return $this->editable;
    }

    public function setEditable(bool $editable): void {
        $this->editable = $editable;
    }
}