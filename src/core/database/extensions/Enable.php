<?php

namespace core\database\extensions;

use core\database\column\Boolean;
use core\database\Entity;
use core\database\Extension;
use core\database\TableDefinition;

const ENABLE_COLUMN_NAME = 'is_enabled';

class Enable implements Extension {
    protected Entity $entity;

    public function setEntity(Entity $entity): void {
        $this->entity = $entity;
    }

    public function isEnabled(): bool {
        return $this->${ENABLE_COLUMN_NAME};
    }

    public function enable(bool $enable = true): void {
        $this->${ENABLE_COLUMN_NAME} = $enable;
    }

    public function disable(): void {
        $this->enable(false);
    }

    public function modifyTable(TableDefinition $table): void {
        $table->addColumn(ENABLE_COLUMN_NAME, (new Boolean())->overrideLabel('Enable'));
    }
}