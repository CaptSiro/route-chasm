<?php

namespace entities;

use core\database\column\PrimaryKey;
use core\database\column\Text;
use core\database\Entity;
use core\database\pdo\PdoTable;
use core\database\StaticTableDefinition;

/**
 * @property string question
 * @property string answer
 */
class Card extends Entity {
    use StaticTableDefinition;

    public static function init(): void {
        static::$definition = new PdoTable(
            'cards',
            [
                "id" => new PrimaryKey(true),
                "question" => Text::getInstance(),
                "answer" => Text::getInstance()
            ],
            'id'
        );
    }
}