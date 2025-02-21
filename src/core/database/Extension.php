<?php

namespace core\database;

interface Extension {
    public function modifyTable(TableDefinition $table): void;

    public function setEntity(Entity $entity): void;
}