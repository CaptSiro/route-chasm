<?php

namespace models\core\Domain;

use components\layout\Grid\description\GridColumn;
use components\layout\Grid\description\GridDescription;
use components\layout\Grid\Loader\ModelGridLoader;
use core\App;
use core\configs\AppConfig;
use core\configs\EnvConfig;
use core\database\sql\DatabaseAction;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\Origin;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\forms\description\NumberField;
use core\forms\description\TextField;
use core\guards\Guard;
use core\guards\NumberGuard;
use core\route\Path;
use core\url\Url;
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

#[Table('core_domain')]
#[Database(App::DATABASE)]
class Domain extends Model implements Enable {
    use EnableExtension;

    public static function getGridDescription(): GridDescription {
        $columns = [];

        self::addEnableGridColumn($columns);
        $columns[DomainProxy::COLUMN_DOMAIN] = new GridColumn('Domain');

        return new GridDescription(
            $columns,
            new ModelGridLoader(static::class),
            new DomainProxy()
        );
    }

    public static function fromEnv(): static {
        $url = Url::from(
            App::getEnvStatic()->getOrDie("DOMAIN_URL")
        );

        $domain = new static();

        $domain->set([
            'host' => $url->getHost(),
            'port' => $url->getPort(),
            'path' => $url->getPath(),
        ]);

        $domain->notSavable();
        return $domain;
    }

    public static function fromUrl(Url $url): static {
        return self::fromUrlString($url->toString());
    }

    public static function fromUrlString(string $url): static {
        $domains = self::all(where: Query::static("is_enabled = 1"));

        foreach ($domains as $domain) {
            if (str_contains($url, $domain->getLiteral())) {
                return $domain;
            }
        }

        return self::fromEnv();
    }



    #[Column('id_domain', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[TextField('Host')]
    #[Column(type: Column::TYPE_STRING)]
    protected string $host;

    #[NumberField('Port')]
    #[Column(type: Column::TYPE_INTEGER)]
    protected int $port;

    #[TextField('Path')]
    #[Column(type: Column::TYPE_STRING)]
    protected string $path;

    #[Column(type: Column::TYPE_INTEGER)]
    protected int $cost;



    public function __toString(): string {
        return $this->getLiteral();
    }

    public function save(): DatabaseAction|View {
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

        $cost = 1 + intval($this->port > 0) + intval($this->path != '');
        if (!isset($this->cost) || $cost !== $this->cost) {
            $this->cost = $cost;
        }

        return parent::save();
    }



    public function getPath(): Path {
        return Path::from($this->path);
    }

    public function getLiteral(): string {
        $ret = $this->host;
        if ($this->port > 0) {
            $ret .= ':'. $this->port;
        }

        if ($this->path !== '') {
            $ret = Path::join($ret, $this->path);
        }

        return $ret;
    }
}