<?php

namespace tables\core;

use core\database\DatabaseColumns;
use core\database\pdo\column\Integer;
use core\database\pdo\column\PrimaryKey;
use core\database\pdo\column\Text;
use core\database\Table;

/**
 * @property string host
 * @property int port
 * @property string path
 * @property int cost
 */
class Domain extends Table {
    use DatabaseColumns;

    public static function init(): void {
        self::$columns = [
            'id' => new PrimaryKey(true),
            'host' => Text::getInstance(),
            'port' => Integer::getInstance(),
            'path' => Text::getInstance(),
            'cost' => Integer::getInstance()
        ];

        parent::init();
    }

    public static function getTable(): string {
        return "core_domains";
    }



    public function save(): void {
        $cost = 1 + intval($this->port !== 0) + intval(!empty($this->path));
        if ($cost !== $this->cost) {
            $this->cost = $cost;
        }

        parent::save();
    }
}