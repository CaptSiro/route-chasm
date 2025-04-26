<?php

namespace core\database_v3\entities;

use core\database_v3\sql\Column;
use core\database_v3\sql\Database;
use core\database_v3\sql\Model;
use core\database_v3\sql\Table;

/**
 * @property int $id
 * @property int $port
 * @property int $cost
 * @property string $host
 * @property string $path
 */

#[Table('core_domains')]
#[Database('app')]
class Domain extends Model {
    #[Column(primaryKey: true)]
    protected int $id;

    #[Column]
    protected string $host;

    #[Column]
    protected int $port;

    #[Column]
    protected string $path;

    #[Column]
    protected int $cost;
}