<?php

namespace models\core\Domain;

use components\layout\Grid\description\GridColumn;
use components\layout\Grid\description\GridDescription;
use components\layout\Grid\Loader\ModelGridLoader;
use core\App;
use core\database\sql\DatabaseAction;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\forms\description\NumberField;
use core\forms\description\TextField;
use core\guards\Guard;
use core\guards\NumberGuard;
use core\route\Path;
use core\RouteChasmEnvironment;
use core\url\Url;
use core\view\View;
use models\extensions\Enable\Enable;
use models\extensions\Enable\EnableExtension;

#[Table('core_domain')]
#[Database(App::DATABASE)]
class Domain extends Model implements Enable {
    public static function getGridDescription(): GridDescription {
        $columns = [];

        self::addEnableGridColumn($columns);
        $columns[DomainProxy::COLUMN_DOMAIN] = new GridColumn('Domain');

        return new GridDescription(
            $columns,
            new ModelGridLoader(static::class, portionSize: ModelGridLoader::getPortionSizeSetting()),
            proxy: new DomainProxy()
        );
    }

    public static function fromEnv(): static {
        $url = Url::from(
            App::getEnvStatic()->getOrDie(RouteChasmEnvironment::ENV_DOMAIN_URL)
        );

        $domain = new static();

        $domain->protocol = $url->getProtocol();
        $domain->host = $url->getHost();
        $domain->port = $url->getPort();
        $domain->path = $url->getPath();

        $domain->notSavable();
        return $domain;
    }

    public static function fromUrl(Url $url): static {
        return self::fromUrlRaw($url->toString());
    }

    public static function fromUrlRaw(string $url): static {
        $domains = self::all(where: Query::static("is_enabled = 1"));

        foreach ($domains as $domain) {
            if (str_contains($url, $domain->getLiteral())) {
                return $domain;
            }
        }

        return self::fromEnv();
    }



    use EnableExtension;

    #[Column('id_domain', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[TextField('Protocol')]
    #[Column(type: Column::TYPE_STRING)]
    public string $protocol;

    #[TextField('Host')]
    #[Column(type: Column::TYPE_STRING)]
    public string $host;

    #[NumberField('Port')]
    #[Column(type: Column::TYPE_INTEGER)]
    public int $port;

    #[TextField('Path')]
    #[Column(type: Column::TYPE_STRING)]
    public string $path;

    #[Column(type: Column::TYPE_INTEGER)]
    public int $cost;



    protected ?Path $pathObject;



    // Model
    public function __toString(): string {
        return $this->getLiteral();
    }

    public function getHumanIdentifier(): string {
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
        if (!isset($this->pathObject)) {
            $this->pathObject = Path::from($this->path);
        }

        return $this->pathObject;
    }

    /**
     * Prepends the domain path to a relative path, producing a full path.
     *
     * This method does not modify the provided $relative object.
     * It returns a new Path instance representing the merged result.
     *
     * @param Path $relative The relative path to attach to the domain path.
     * @return Path A new Path instance with the domain path prepended.
     */
    public function attach(Path $relative): Path {
        return Path::merge($this->getPath(), $relative);
    }

    /**
     * Removes the domain path prefix from a full path, producing a relative path.
     *
     * This method does not modify the provided $full object.
     * It returns a new Path instance with the offset adjusted to skip the domain prefix.
     *
     * @param Path $full The full path containing the domain prefix.
     * @return Path A new Path instance with the domain path detached.
     */
    public function detach(Path $full): Path {
        return $full
            ->copy()
            ->setOffset($this->getPath()->getOffset());
    }

    public function createUrl(?Path $relative = null): Url {
        $url = new Url();

        $url
            ->setProtocol($this->protocol)
            ->setHost($this->host);

        if ($this->port > 0) {
            $url->setPort($this->port);
        }

        $url->setPath(
            is_null($relative)
                ? $this->getPath()->copy()
                : $this->attach($relative)
        );

        return $url;
    }

    public function getLiteral(): string {
        $ret = $this->protocol .'://'. $this->host;
        if ($this->port > 0) {
            $ret .= ':'. $this->port;
        }

        if ($this->path !== '') {
            $ret = Path::join($ret, $this->path);
        }

        return $ret;
    }
}