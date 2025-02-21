<?php

namespace entities\core;

use core\database\column\Integer;
use core\database\column\PrimaryKey;
use core\database\column\Text;
use core\database\Entity;
use core\database\extensions\Enable;
use core\database\Schema;
use core\database\pdo\PdoTable;
use modules\forms\controls\TextArea\TextArea;
use modules\forms\definition\FormDefinition;
use modules\forms\definition\overrides\FieldOverride;



Entity::addSchema(Domain::class, new Schema(
    new PdoTable(
        'core_domains',
        [
            'id' => new PrimaryKey(true),
            'host' => Text::getInstance(),
            'port' => Integer::getInstance(),
            'path' => Text::getInstance(),
            'cost' => Integer::getInstance(),
        ],
        'id'
    ),
    new FormDefinition(
        [
            'host' => new FieldOverride('HOST', new TextArea()),
        ],
    ),
    [new Enable()]
));



/**
 * @property string host
 * @property int port
 * @property string path
 * @property int cost
 */
class Domain extends Entity {
    public function save(): void {
        $cost = 1 + intval($this->port !== 0) + intval(!empty($this->path));
        if ($cost !== $this->cost) {
            $this->cost = $cost;
        }

        parent::save();
    }
}