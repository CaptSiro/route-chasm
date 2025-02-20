<?php

namespace tests\utils\RouteChasm;

use core\database\Entity;
use core\database\Schema;
use core\database\pdo\PdoTable;

Entity::addSchema(TestEntity::class, new Schema(
    new PdoTable('test', []),
));

class TestEntity extends Entity {

}