<?php

namespace core\database\sql\parameter;

use core\database\sql\SqlDatabase;
use PDO;

trait SqlParamType {
    function getType(): int {
        $type = gettype($this->value);

        if (!isset(SqlDatabase::TYPES[$type])) {
            return PDO::PARAM_STR;
        }

        return SqlDatabase::TYPES[$type];
    }
}