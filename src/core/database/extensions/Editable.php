<?php

namespace core\database\extensions;

use core\database\column\Boolean;
use core\database\Entity;
use core\database\Extension;
use core\database\TableDefinition;

const EDITABLE_COLUMN_NAME = 'is_editable';

class Editable implements Extension {
    protected Entity $entity;

    public function setEntity(Entity $entity): void {
        $this->entity = $entity;
    }

    public function isEditable(): bool {
        return $this->${EDITABLE_COLUMN_NAME};
    }

    public function setEditable(bool $editable): void {
        $this->${EDITABLE_COLUMN_NAME} = $editable;
    }

    public function modifyTable(TableDefinition $table): void {
        $table->addColumn(EDITABLE_COLUMN_NAME, (new Boolean())->overrideLabel('Is editable'));
    }
}