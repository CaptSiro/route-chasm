<?php

namespace core\utils;

use core\database\sql\DatabaseAction;
use core\view\View;

class Components {
    public static function nullifyDatabaseAction(DatabaseAction|View $result): ?View {
        if (!($result instanceof View)) {
            return null;
        }

        return $result;
    }
}