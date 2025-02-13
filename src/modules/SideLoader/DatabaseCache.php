<?php

namespace modules\SideLoader;

use core\database\column\PrimaryKey;
use core\database\column\Text;
use core\database\Entity;
use core\database\pdo\PdoTable;
use core\database\query\Query;

/**
 * @property int id
 * @property string hash
 * @property string path
 */
class DatabaseCache extends Entity {
    public static function init(): void {
        static::$definition = new PdoTable(
            'module_sideloadercache',
            [
                'id' => new PrimaryKey(true),
                'hash' => Text::getInstance(),
                'path' => Text::getInstance()
            ],
            'id'
        );
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