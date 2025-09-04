<?php

namespace core\utils;

use core\database\sql\Model;

class Models {
    /**
     * @param array<Model> $models
     * @return array<int, Model>
     */
    public static function identity(array $models): array {
        $ret = [];

        foreach ($models as $model) {
            $ret[$model->getId()] = $model;
        }

        return $ret;
    }
}