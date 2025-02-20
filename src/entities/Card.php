<?php

namespace entities;

use core\database\column\PrimaryKey;
use core\database\column\Text;
use core\database\Entity;
use core\database\Schema;
use core\database\pdo\PdoTable;



Entity::addSchema(Card::class, new Schema(
    new PdoTable(
        'cards',
        [
            "id" => new PrimaryKey(true),
            "question" => Text::getInstance(),
            "answer" => Text::getInstance()
        ],
        'id'
    )
));



/**
 * @property string question
 * @property string answer
 */
class Card extends Entity {}
