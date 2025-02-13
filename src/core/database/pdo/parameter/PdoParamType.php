<?php

namespace core\database\pdo\parameter;

use core\database\pdo\PdoDatabase;
use PDO;

trait PdoParamType {
    function getType(): int {
        $type = gettype($this->value);

        if (!isset(PdoDatabase::TYPES[$type])) {
            return PDO::PARAM_STR;
        }

        return PdoDatabase::TYPES[$type];
    }
}