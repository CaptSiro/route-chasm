<?php

namespace entities\core;

use core\database\column\Integer;
use core\database\column\PrimaryKey;
use core\database\column\Text;
use core\database\Entity;
use core\database\extensions\Enable;
use core\database\Schema;
use core\database\sql\SqlTable;
use core\guards\Guard;
use core\guards\NumberGuard;
use core\view\View;
use modules\forms\definition\FormDefinition;
use modules\forms\definition\overrides\Discard;



Entity::addSchema(Domain::class, new Schema(
    new SqlTable(
        'core_domains',
        [
            'id' => new PrimaryKey(true),
            'host' => (new Text())->overrideName('Host'),
            'port' => (new Integer())->overrideName('Port'),
            'path' => (new Text())->overrideName('Path'),
            'cost' => new Integer(),
        ],
        'id'
    ),
    new FormDefinition(['cost' => new Discard()]),
    [new Enable()]
));



/**
 * @property string host
 * @property int port
 * @property string path
 * @property int cost
 */
class Domain extends Entity {
    public function save(): ?View {
        $guards = [
            NumberGuard::inRange(
                strlen($this->host), 0, 255,
                'host', 'Host is longer than 255 characters'
            ),
            NumberGuard::inRange(
                strlen($this->path), 0, 255,
                'path', 'Path is longer than 255 characters'
            ),
            NumberGuard::inRange(
                $this->port, 0, 65535,
                'port', 'Port is larger than 65535'
            ),
        ];

        if ($result = Guard::testGroup($guards)) {
            return $result;
        }

        $cost = 1 + intval($this->port != 0) + intval($this->path != '');
        if ($cost !== $this->cost) {
            $this->cost = $cost;
        }

        return parent::save();
    }
}