<?php

namespace modules\SideLoader;

use core\database\column\PrimaryKey;
use core\database\column\Text;
use core\database\Entity;
use core\database\pdo\PdoTable;
use core\database\query\Query;
use core\database\StaticTableDefinition;
use core\utils\Strings;

/**
 * @property int id
 * @property string hash
 * @property string path
 */
class DatabaseCache extends Entity {
    use StaticTableDefinition;

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



    public static function fromHash(string $hash): ?static {
        return static::fetch(
            Query::raw("hash = ?", [$hash])
        );
    }

    public static function fromPath(string $path): ?static {
        return static::fetch(
            Query::raw('path = ?', [$path])
        );
    }

    public static function generateHash(int $retries, int &$length): string {
        $attempt = 0;
        $hash = Strings::randomBase64($length);

        do {
            $record = self::fromHash($hash);
            if (is_null($record)) {
                break;
            }

            $attempt++;

            if ($attempt >= $retries) {
                $length++;
                $attempt = 0;
            }
        } while (true);

        return $hash;
    }
}