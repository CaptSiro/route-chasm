<?php

namespace entities;

use core\database\column\PrimaryKey;
use core\database\column\Text;
use core\database\Entity;
use core\database\pdo\PdoTable;

/**
 * @property string question
 * @property string answer
 */
class Card extends Entity {
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