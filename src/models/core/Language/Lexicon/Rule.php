<?php

namespace models\core\Language\Lexicon;

use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\ModelCache;
use core\database\sql\Table;
use core\forms\description\TextField;

#[Grid]
#[Table('core_lexicon_rule')]
#[Database(App::DATABASE)]
class Rule extends Model {
    use ModelCache;

    public static function fromRule(string $rule, bool $create = false): ?static {
        static::modelCache_loadAll(fn(Rule $x) => $x->rule);
        $instance = static::modelCache_get($rule);

        if (is_null($instance) && $create) {
            $instance = static::create(['rule' => $rule]);
            static::modelCache_set($rule, $instance);
        }

        return $instance;
    }

    public static function fromLabel(string $label): ?static {
        static::modelCache_loadAll(fn(Rule $x) => $x->rule);

        foreach (self::$modelCache as $rule) {
            if ($rule->label === $label) {
                return $rule;
            }
        }

        return null;
    }

    public static function options(): array {
        static::modelCache_loadAll(fn(Rule $x) => $x->rule);

        $ret = [];
        foreach (self::$modelCache as $record) {
            $ret[$record->getId()] = $record->getLabel();
        }

        return $ret;
    }

    public static function getLabels(): array {
        static::modelCache_loadAll(fn(Rule $x) => $x->rule);

        $ret = [];
        foreach (self::$modelCache as $record) {
            $ret[] = $record->getLabel();
        }

        return $ret;
    }



    #[Column('id_rule', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[TextField]
    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    public string $rule;

    #[TextField]
    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    public ?string $label;



    // Model
    public function getHumanIdentifier(): string {
        return $this->getLabel();
    }



    public function match(string $value): bool {
        return preg_match($this->rule, $value);
    }

    public function getLabel(): string {
        if (is_null($this->label) || $this->label === '') {
            return $this->rule;
        }

        return $this->label;
    }
}