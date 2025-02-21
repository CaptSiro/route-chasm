<?php

namespace tests\utils\RouteChasm;

use core\database\Entity;
use core\database\Schema;
use core\database\sql\SqlTable;

Entity::addSchema(TestEntity::class, new Schema(
    new SqlTable('test', []),
));

class TestEntity extends Entity {

}