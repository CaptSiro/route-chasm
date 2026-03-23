<?php

namespace models\core\Setting;

use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\DatabaseAction;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\forms\description\TextField;
use core\utils\Strings;
use core\view\View;
use models\extensions\Editable\Editable;
use models\extensions\Editable\EditableExtension;

#[Grid(proxy: new SettingProxy())]
#[Table('core_setting')]
#[Database(App::DATABASE)]
final class Setting extends Model implements Editable {
    /**
     * Returns setting that saved under given name. Use <code>create: true</code> and <code>default: <value></code> to
     * create default setting if it is not present
     *
     * @param string $name
     * @param bool $create
     * @param string|null $default
     * @param array<string, mixed> $properties
     * @return static|null
     */
    public static function fromName(string $name, bool $create = false, mixed $default = null, array $properties = []): ?self {
        $setting = self::first(where: new Query('name = ?', [Parameter::infer($name)]));
        if (!is_null($setting) || !$create) {
            return $setting;
        }

        $setting = new self();
        $setting->set($properties);
        $setting->name = $name;
        $setting->value = $default;

        $setting->save();

        return $setting;
    }



    use EditableExtension;

    #[Column('id_setting', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[TextField('Setting', readonly: true)]
    #[GridColumn('Setting')]
    #[Column(type: Column::TYPE_STRING)]
    public string $name;

    #[TextField]
    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    public mixed $value;



    // Model
    public function getHumanIdentifier(): string {
        return $this->name;
    }

    public function save(): View|DatabaseAction {
        if (!(gettype($this->value) === 'string')) {
            $this->value = (string) $this->value;
        }

        return parent::save();
    }

    public function toInt(): int {
        return intval($this->value);
    }

    public function toBoolean(): bool {
        return Strings::fromHumanReadableBoolean($this->value);
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