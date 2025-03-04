<?php

namespace core\database;

use core\database\query\Query;

interface EntityFactory {
    public function fetch(string|Query|null $additional = null): ?Entity;

    /**
     * @return array<Entity>
     */
    public function fetchAll(string|Query|null $additional = null): array;

    public function fromId(mixed $id): Entity;

    public function fromRow(array|false $row): ?Entity;

    public function getIdColumn(): string;

    public function getSchema(): Schema;
}