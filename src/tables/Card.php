<?php

namespace tables;

use core\database\DatabaseColumns;
use core\database\pdo\column\PrimaryKey;
use core\database\pdo\column\Text;
use core\database\Table;

/**
 * @property string question
 * @property string answer
 */
class Card extends Table {
    use DatabaseColumns;

    public static function init(): void {
        self::$columns = [
            "id" => new PrimaryKey(true),
            "question" => Text::getInstance(),
            "answer" => Text::getInstance()
        ];

        parent::init();
    }

    public static function getTable(): string {
        return "cards";
    }
}