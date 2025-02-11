<?php

namespace modules\SideLoader;

use core\database\DatabaseColumns;
use core\database\pdo\column\PrimaryKey;
use core\database\pdo\column\Text;
use core\database\query\Query;
use core\database\Table;

/**
 * @property int id
 * @property string hash
 * @property string path
 */
class DatabaseCache extends Table {
    use DatabaseColumns;

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



    public static function fromHash(string $hash, bool $create = false): ?static {
        return self::createConditionally(
            self::fetch(
                Query::raw("hash = ?", [$hash])
            ),
            $create
        );
    }
}