<?php

namespace entities;

use core\database\DatabaseColumns;
use core\database\pdo\column\PrimaryKey;
use core\database\pdo\column\Text;
use core\database\Entity;

/**
 * @property string question
 * @property string answer
 */
class Card extends Entity {
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