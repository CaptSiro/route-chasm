<?php

namespace tables;

use core\database\column\PrimaryKey;
use core\database\column\Text;
use core\database\Table;

/**
 * @property string question
 * @property string answer
 */
class Card extends Table {
    protected static array $columns;

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

    public static function getColumns(): array {
        return self::$columns;
    }
}