<?php

namespace models\core;

use core\App;
use core\database_v3\sql\Action;
use core\database_v3\sql\Column;
use core\database_v3\sql\Database;
use core\database_v3\sql\Model;
use core\database_v3\sql\query\Parameter;
use core\database_v3\sql\query\Query;
use core\database_v3\sql\Table;
use core\view\View;
use models\extensions\Editable\Editable;
use models\extensions\Editable\EditableExtension;

/**
 * @property string $name
 * @property string|null $value
 */

#[Table('core_settings')]
#[Database(App::DATABASE)]
final class Setting extends Model implements Editable {
    use EditableExtension;

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
        $setting = self::first(where: new Query('name = ?', [Parameter::infer($name)]));
        if (!is_null($setting) || !$create) {
            return $setting;
        }

        $setting = new static();
        $setting->name = $name;
        $setting->value = $default;
        $setting->save();

        return $setting;
    }



    #[Column(type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[Column(type: Column::TYPE_STRING)]
    protected string $name;

    #[Column(type: Column::TYPE_STRING)]
    protected mixed $value;



    public function save(): View|Action {
        if (!(gettype($this->value) === 'string')) {
            $this->value = (string) $this->value;
        }

        return parent::save();
    }

    public function toInt(): int {
        return intval($this->value);
    }

    public function toBoolean(): bool {
        return boolval($this->value);
    }

    public function toFloat(): float {
        return floatval($this->value);
    }

    public function toDouble(): float {
        return doubleval($this->value);
    }

    public function toString(): string {
        return $this->value ?? '';
    }

    public function __toString(): string {
        return $this->name .': '. $this->value;
    }
}