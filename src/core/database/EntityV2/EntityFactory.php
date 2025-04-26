<?php

namespace core\database\EntityV2;

use core\database\query\Query;
use core\database\Schema;

class EntityFactory {
    protected Schema $schema;

    public function setSchema(Schema $schema): static {
        $this->schema = $schema;
        return $this;
    }

    public function fromId(mixed $id): Entity {

    }

    public function fromRow(array $row): Entity;

    public function create(): Entity;

    public function fetch(string|Query|null $additional = null): ?Entity;

    public function fetchAll(string|Query|null $additional = null): ?Entity;
}
