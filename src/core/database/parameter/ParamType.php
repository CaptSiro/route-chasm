<?php

namespace core\database\parameter;

use core\database\Database;
use PDO;

trait ParamType {
    function getType(): int {
        $type = gettype($this->value);

        if (!isset(Database::TYPE_TABLE[$type])) {
            return PDO::PARAM_STR;
        }

        return Database::TYPE_TABLE[$type];
    }
}