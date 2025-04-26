<?php

namespace core\database_v3\entities;

use core\database_v3\sql\Column;
use core\database_v3\sql\Database;
use core\database_v3\sql\Model;
use core\database_v3\sql\Table;



#[Table("core_settings")]
#[Database("app")]
class Setting extends Model {
    #[Column("id", Column::TYPE_INTEGER, true)]
    public int $id;

    #[Column("name", Column::TYPE_STRING)]
    public string $name;

    #[Column("value", Column::TYPE_STRING)]
    public string $value;
}