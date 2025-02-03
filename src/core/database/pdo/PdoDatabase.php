<?php

namespace core\database\pdo;

use core\App;
use core\database\Database;
use core\database\pdo\config\PdoConfig;
use core\database\query\Query;
use core\database\SideEffect;
use core\database\Table;
use core\Singleton;
use http\Exception\InvalidArgumentException;
use PDO;
use PDOStatement;
use stdClass;

class PdoDatabase implements Database {
    use Singleton;

    public const TYPE_TABLE = [
        "boolean" => PDO::PARAM_BOOL,
        "integer" => PDO::PARAM_INT,
        "double" => PDO::PARAM_STR,
        "string" => PDO::PARAM_STR,
        "NULL" => PDO::PARAM_NULL,
    ];

    protected static ?PdoConfig $config;

    public static function configure(PdoConfig $config): void {
        self::$config = $config;
    }

    private static function countParams(string $sql): int {
        $no_string_literals = "";

        $in_string = false;
        for ($i = 0; $i < strlen($sql); $i++) {
            if (in_array($sql[$i], ["'", '"'])) {
                $in_string = !$in_string;
                continue;
            }

            if ($in_string === true) {
                continue;
            }

            $no_string_literals .= $sql[$i];
        }

        return intval(preg_match_all("/([:?])/", $no_string_literals));
    }

    /**
     * @throws MixedIndexingException
     */
    private static function bind(PDOStatement $statement, string|Query $query): void {
        $index = 1;
        $indexationType = "--initial--";

        $i = 0;
        $count = self::countParams(Query::unwrapLiteral($query));
        $buffer = Query::unwrapBuffer($query);

        while ($i < $count && !$buffer->isEmpty()) {
            $param = $buffer->shift();
            $name = $param->getName() ?? $index++;

            if ($indexationType !== gettype($name) && $indexationType !== "--initial--") {
                throw new MixedIndexingException("Cannot use named param logic as well as indexed param logic. Got index: " . $name);
            }

            $indexationType = gettype($name);

            $statement->bindValue($name, $param->getValue(), $param->getType());
            $i++;
        }
    }



    protected PDO $connection;



    public function __construct(?PdoConfig $config = null) {
        if (is_null($config)) {
            $config = self::$config;
        }

        if (!isset($config)) {
            $config = App::getInstance()
                ->getConfig()
                ->getPdoConfig();
        }

        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // errors from MySQL will appear as PHP Exceptions
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => false, // SQL injection
        ];
        $this->connection = new PDO(
            $config->getConnectionString(),
            $config->getDatabaseUser(),
            $config->getDatabasePassword(),
            $opt
        );
    }



    public function getConnection(): PDO {
        return $this->connection;
    }

    /**
     * Run a query that does not return any rows such as UPDATE, DELETE, INSERT or TRUNCATE.
     *
     * @param string|Query $query
     * @return SideEffect
     * @throws MixedIndexingException
     */
    public function run(string|Query $query): SideEffect {
        $stmt = $this->connection->prepare(Query::unwrapLiteral($query));
        self::bind($stmt, $query);

        $stmt->execute();

        return new SideEffect(
            $this->getLastInsertedId(),
            $stmt->rowCount()
        );
    }

    public function getLastInsertedId(): false|string {
        return $this->connection->lastInsertId();
    }

    /**
     * Fetch a single row.
     *
     * @param string|Query $query
     * @param string $class
     * @return Table
     * @throws MixedIndexingException
     */
    public function fetch(string|Query $query, string $class): ?Table {
        $stmt = $this->connection->prepare(Query::unwrapLiteral($query));

        self::bind($stmt, $query);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        if (!method_exists($class, "fromRow")) {
            throw new InvalidArgumentException("Provided class '$class' must have implementation of static function: 'fromRow'");
        }

        return call_user_func("$class::fromRow", $stmt->fetch());
    }

    /**
     * Fetch multiple rows.
     *
     * @param string|Query $query
     * @param string $class
     * @return ?array
     * @throws MixedIndexingException
     */
    public function fetchAll(string|Query $query, string $class = stdClass::class): ?array {
        $stmt = $this->connection->prepare(Query::unwrapLiteral($query));

        self::bind($stmt, $query);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $rows = $stmt->fetchAll();
        if ($rows === false) {
            return null;
        }

        if (!method_exists($class, "fromRow")) {
            throw new InvalidArgumentException("Provided class '$class' must have implementation of static function: 'fromRow'");
        }

        return array_map(fn($x) => call_user_func("$class::fromRow", $x), $rows);
    }
}