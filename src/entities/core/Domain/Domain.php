<?php

namespace entities\core\Domain;

use components\layout\Grid\ColumnLayout;
use components\layout\Grid\GridLayout;
use core\database\column\Integer;
use core\database\column\PrimaryKey;
use core\database\column\Text;
use core\database\Entity;
use core\database\extensions\Enable;
use core\database\Schema;
use core\database\sql\SqlTable;
use core\guards\Guard;
use core\guards\NumberGuard;
use core\path\Path;
use core\view\View;
use modules\forms\definition\FormDefinition;
use modules\forms\definition\overrides\Discard;
use const core\database\extensions\ENABLE_COLUMN_NAME;



$schema = new Schema(
    new SqlTable(
        'core_domains',
        [
            'id' => new PrimaryKey(true),
            'host' => (new Text())->overrideLabel('Host'),
            'port' => (new Integer())->overrideLabel('Port'),
            'path' => (new Text())->overrideLabel('Path'),
            'cost' => new Integer(),
        ],
        'id'
    ),
    new FormDefinition(['cost' => new Discard()]),
    [new Enable()]
);
Entity::addSchema(Domain::class, $schema);



/**
 * @property string host
 * @property int port
 * @property string path
 * @property int cost
 */
class Domain extends Entity {
    public static function defaultTableLayout(): GridLayout {
        return new GridLayout(
            [
                ENABLE_COLUMN_NAME => new ColumnLayout('Enabled', '96px'),
                DomainProxy::COLUMN_DOMAIN => new ColumnLayout('Domain', '1fr')
            ],
            new DomainProxy()
        );
    }

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

    public function getLiteral(): string {
        $ret = $this->host;
        if ($this->port !== 0) {
            $ret .= ':'. $this->port;
        }

        if ($this->path !== '') {
            $ret = Path::join($ret, $this->path);
        }

        return $ret;
    }

    public function __toString(): string {
        return $this->getLiteral();
    }
}