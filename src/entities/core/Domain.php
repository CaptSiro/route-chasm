<?php

namespace entities\core;

use components\core\SaveException\SaveError;
use core\database\column\Integer;
use core\database\column\PrimaryKey;
use core\database\column\Text;
use core\database\Entity;
use core\database\extensions\Enable;
use core\database\Schema;
use core\database\sql\SqlTable;
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
    public function save(): ?SaveError {
        if (strlen($this->host) > 255) {
            return new SaveError(
                'host',
                'Host is longer than 255 characters'
            );
        }

        if (strlen($this->path) > 255) {
            return new SaveError(
                'path',
                'Path is longer than 255 characters'
            );
        }

        if ($this->port > 65535) {
            return new SaveError(
                'port',
                'Port is larger than 65535'
            );
        }

        $cost = 1 + intval($this->port != 0) + intval($this->path != '');
        if ($cost !== $this->cost) {
            $this->cost = $cost;
        }

        return parent::save();
    }
}