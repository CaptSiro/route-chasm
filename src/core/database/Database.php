<?php

namespace core\database;

use core\database\buffer\Buffer;
use core\database\buffer\ParamBuffer;
use core\database\config\Config;
use core\database\query\Query;
use core\Singleton;
use http\Exception\InvalidArgumentException;
use PDO;
use PDOStatement;
use stdClass;

class Database {
    use Singleton;

    public const TYPE_TABLE = [
        "boolean" => PDO::PARAM_BOOL,
        "integer" => PDO::PARAM_INT,
        "double" => PDO::PARAM_STR,
        "string" => PDO::PARAM_STR,
        "NULL" => PDO::PARAM_NULL,
    ];

    protected static Config $config;

    public static function configure(Config $config): void {
        self::$config = $config;
    }

    private static function getLiteral(string|Query $query): string {
        if ($query instanceof Query) {
            return $query->getLiteral();
        }

        return $query;
    }

    /**
     * @param string|Query $query
     * @return Buffer
     */
    private static function getBuffer(string|Query $query): Buffer {
        if ($query instanceof Query) {
            return $query->getParams();
        }

        return ParamBuffer::getInstance();
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

        $iteration = 0;
        $count = self::countParams(self::getLiteral($query));
        $buf = self::getBuffer($query);

        while ($iteration < $count && !$buf->isEmpty()) {
            $param = $buf->shift();
            $name = $param->getName() ?? $index++;

            if ($indexationType !== gettype($name) && $indexationType !== "--initial--") {
                throw new MixedIndexingException("Cannot use named param logic as well as indexed param logic. Got index: " . $name);
            }

            $indexationType = gettype($name);

            $statement->bindValue($name, $param->getValue(), $param->getType());
            $iteration++;
        }
    }



    protected PDO $connection;



    /**
     * @throws CreationException
     */
    public function __construct() {
        if (!isset(self::$config)) {
            throw new CreationException("Must set config object before creating connection to database. Use Database::configure() with custom object that implements Config or use BasicConfig class.");
        }

        $connectionString = "mysql:host=" . self::$config->getDatabaseHost()
            . ";port=" . self::$config->getDatabasePort()
            . ";dbname=" . self::$config->getDatabaseName()
            . ";charset=" . self::$config->getDatabaseCharset();
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // errors from MySQL will appear as PHP Exceptions
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => false, // SQL injection
        ];
        $this->connection = new PDO($connectionString, self::$config->getDatabaseUser(), self::$config->getDatabasePassword(), $opt);
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
        $stmt = $this->connection->prepare(self::getLiteral($query));
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
        $stmt = $this->connection->prepare(self::getLiteral($query));

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
        $stmt = $this->connection->prepare(self::getLiteral($query));

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