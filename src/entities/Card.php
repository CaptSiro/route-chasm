<?php

namespace entities;

use core\database\column\PrimaryKey;
use core\database\column\Text;
use core\database\Entity;
use core\database\EntityDefinition;
use core\database\pdo\PdoTable;



Entity::addDefinition(Card::class, new EntityDefinition(
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
