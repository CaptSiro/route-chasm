<?php

namespace models;

use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\utils\Strings;

#[Table('core_sideloader')]
#[Database(App::DATABASE)]
class SideLoaderRecord extends Model {
    public static function fromHash(string $hash): ?static {
        return static::first(
            where: Query::infer('hash = ?', $hash),
        );
    }

    public static function fromPath(string $path): ?static {
        return static::first(
            where: Query::infer('path = ?', $path),
        );
    }

    /**
     * Batch variant of fromPath(): one `WHERE path IN (...)` query instead of one per path.
     *
     * @param array<string> $paths
     * @return array<string, static> keyed by path
     */
    public static function fromPaths(array $paths): array {
        if (empty($paths)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($paths), '?'));
        $ret = [];

        foreach (static::all(where: Query::infer("path IN ($placeholders)", ...array_values($paths))) as $record) {
            $ret[$record->path] = $record;
        }

        return $ret;
    }

    public static function generateHash(int $retries, int &$length): string {
        $attempt = 0;

        do {
            // [Claude review] Bug fix: the hash was generated once before the loop and never regenerated, so a single
            // collision with an existing record made this loop forever (hanging the request), even after $length
            // was increased. A fresh hash is now drawn on every attempt.
            $hash = Strings::randomBase64($length);
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
    public int $id;

    #[Column(type: Column::TYPE_STRING)]
    public string $hash;

    #[Column(type: Column::TYPE_STRING)]
    public string $path;
}