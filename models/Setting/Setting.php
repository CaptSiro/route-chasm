<?php

namespace models\Setting;

use components\forms\description\TextField;
use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\DatabaseAction;
use core\database\sql\Model;
use core\database\sql\Table;
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
        // [Claude review] All settings (a handful of rows) are loaded once per request instead of 1 query per name
        if (is_null(self::$byName)) {
            self::$byName = [];

            foreach (self::all() as $loaded) {
                self::$byName[$loaded->name] = $loaded;
            }
        }

        $setting = self::$byName[$name] ?? null;
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

    /** @var array<string, Setting>|null per-request cache for fromName(), reset on save/delete */
    private static ?array $byName = null;

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

        self::$byName = null; // [Claude review] invalidate fromName() cache (name may have changed)
        return parent::save();
    }

    public function delete(): DatabaseAction {
        self::$byName = null; // [Claude review] invalidate fromName() cache
        return parent::delete();
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