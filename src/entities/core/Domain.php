<?php

namespace entities\core;

use core\database\column\Integer;
use core\database\column\PrimaryKey;
use core\database\column\Text;
use core\database\Entity;
use core\database\pdo\PdoTable;

/**
 * @property string host
 * @property int port
 * @property string path
 * @property int cost
 */
class Domain extends Entity {
    public static function init(): void {
        static::$definition = new PdoTable(
            'core_domains',
            [
                'id' => new PrimaryKey(true),
                'host' => Text::getInstance(),
                'port' => Integer::getInstance(),
                'path' => Text::getInstance(),
                'cost' => Integer::getInstance()
            ],
            'id'
        );
    }


    public function save(): void {
        $cost = 1 + intval($this->port !== 0) + intval(!empty($this->path));
        if ($cost !== $this->cost) {
            $this->cost = $cost;
        }

        parent::save();
    }
}