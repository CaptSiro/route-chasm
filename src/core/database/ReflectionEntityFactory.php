<?php

namespace core\database;

use core\database\query\Query;

class ReflectionEntityFactory implements EntityFactory {
    public function __construct(
        protected string $entityClass
    ) {}



    public function fetch(Query|string|null $additional = null): ?Entity {
        return call_user_func_array([$this->entityClass, 'fetch'], [$additional]);
    }

    public function fetchAll(Query|string|null $additional = null): array {
        return call_user_func_array([$this->entityClass, 'fetchAll'], [$additional]);
    }

    public function fromId(mixed $id): Entity {
        return call_user_func_array([$this->entityClass, 'fromId'], [$id]);
    }

    public function fromRow(false|array $row): ?Entity {
        return call_user_func_array([$this->entityClass, 'fromRow'], [$row]);
    }

    public function getIdColumn(): string {
        return call_user_func_array([$this->entityClass, 'getIdColumn'], []);
    }

    public function getSchema(): Schema {
        return call_user_func_array([$this->entityClass, 'getSchema'], []);
    }
}