<?php

namespace tests\utils\RouteChasm;

use core\database\Entity;
use core\database\EntityDefinition;
use core\database\pdo\PdoTable;

Entity::addDefinition(TestEntity::class, new EntityDefinition(
    new PdoTable('test', []),
));

class TestEntity extends Entity {

}