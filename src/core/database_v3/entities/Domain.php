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
    #[Column('id', Column::TYPE_INTEGER, true)]
    protected int $id;

    #[Column('host', Column::TYPE_STRING)]
    protected string $host;

    #[Column('port', Column::TYPE_INTEGER)]
    protected int $port;

    #[Column('path', Column::TYPE_STRING)]
    protected string $path;

    #[Column('cost', Column::TYPE_INTEGER)]
    protected int $cost;
}