<?php

namespace modules\SideLoader;

use core\database\pdo\column\PrimaryKey;
use core\database\pdo\column\Text;
use core\database\pdo\parameter\PdoPrimitiveParam;
use core\database\query\Query;
use core\database\Table;

class DatabaseCache extends Table {
    protected static array $columns;

    public static function init(): void {
        self::$columns = [
            'id' => new PrimaryKey(true),
            'hash' => Text::getInstance(),
            'path' => Text::getInstance()
        ];

        self::$idColumn = 'id';
        parent::init();
    }

    public static function getTable(): string {
        return 'module_sideloadercache';
    }

    public static function getColumns(): array {
        return self::$columns;
    }

    public static function fromHash(string $hash): static {
        $hashParam = new PdoPrimitiveParam($hash);
        return self::fetch(
            Query::build()->use("hash = $hashParam")
        );
    }
}