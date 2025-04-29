<?php

namespace modules\SideLoader;

use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\utils\Strings;

/**
 * @property string hash
 * @property string path
 */

#[Table('sideloader_cache')]
#[Database(App::DATABASE)]
class CacheRecord extends Model {
    public static function fromHash(string $hash): ?static {
        return static::first(
            where: Query::infer('hash = ?', [$hash]),
        );
    }

    public static function fromPath(string $path): ?static {
        return static::first(
            where: Query::infer('path = ?', [$path]),
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



    #[Column('id_cache', Column::TYPE_INTEGER, true)]
    protected int $id;

    #[Column(type: Column::TYPE_STRING)]
    protected string $hash;

    #[Column(type: Column::TYPE_STRING)]
    protected string $path;
}