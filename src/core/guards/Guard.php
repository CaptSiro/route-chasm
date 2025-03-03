<?php

namespace core\guards;

use components\core\SaveError\Group\SaveErrorGroup;
use components\core\SaveError\SaveError;

class Guard {
    /**
     * @param array<null|SaveError> $guards
     * @return false|SaveErrorGroup
     */
    public static function testGroup(array $guards): false|SaveErrorGroup {
        $errors = array_filter($guards, fn($x) => !is_null($x));
        if (empty($errors)) {
            return false;
        }

        return new SaveErrorGroup($errors);
    }
}