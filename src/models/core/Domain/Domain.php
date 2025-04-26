<?php

namespace models\core\Domain;

use core\App;
use core\database_v3\sql\Action;
use core\database_v3\sql\Column;
use core\database_v3\sql\Database;
use core\database_v3\sql\Model;
use core\database_v3\sql\Table;
use core\guards\Guard;
use core\guards\NumberGuard;
use core\path\Path;
use core\view\View;
use models\extensions\Enable\Enable;
use models\extensions\Enable\EnableExtension;

/**
 * @property int $id
 * @property string $host
 * @property int $port
 * @property string $path
 * @property int $cost
 */

#[Table('core_domains')]
#[Database(App::DATABASE)]
class Domain extends Model implements Enable {
    use EnableExtension;

    #[Column(type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[Column(type: Column::TYPE_STRING)]
    protected string $host;

    #[Column(type: Column::TYPE_INTEGER)]
    protected int $port;

    #[Column(type: Column::TYPE_STRING)]
    protected string $path;

    #[Column(type: Column::TYPE_INTEGER)]
    protected int $cost;



    public function save(): Action|View {
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