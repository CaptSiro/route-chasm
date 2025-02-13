<?php

namespace entities\core;

use core\database\column\Boolean;
use core\database\column\PrimaryKey;
use core\database\column\Text;
use core\database\Entity;
use core\database\pdo\PdoTable;
use core\database\query\Query;

/**
 * @property int id
 * @property string name
 * @property string|null value
 * @property bool editable
 * @final
 */
class Setting extends Entity {
    public static function init(): void {
        static::$definition = new PdoTable(
            'core_settings',
            [
                'id' => new PrimaryKey(true),
                'name' => Text::getInstance(),
                'value' => Text::getInstance(),
                'editable' => Boolean::getInstance()
            ],
            'id'
        );
    }



    /**
     * Returns setting that saved under given name. Use <code>create: true</code> and <code>default: <value></code> to
     * create default setting if it is not present
     *
     * @param string $name
     * @param bool $create
     * @param string|null $default
     * @return static|null
     */
    public static function fromName(string $name, bool $create = false, mixed $default = null): ?static {
        $setting = self::fetch(Query::raw('name = ?', [$name]));
        if (!is_null($setting) || !$create) {
            return $setting;
        }

        $setting = new static();
        $setting->name = $name;
        $setting->value = $default;
        $setting->save();

        return $setting;
    }



    public function save(): void {
        if (!(is_null($this->value) || gettype($this->value) === 'string')) {
            $this->value = ''. $this->value;
        }

        parent::save();
    }

    public function asInt(): int {
        return intval($this->value);
    }

    public function asBool(): bool {
        return boolval($this->value);
    }

    public function asFloat(): float {
        return floatval($this->value);
    }

    public function asString(): string {
        return $this->value ?? '';
    }
}