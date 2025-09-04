<?php

namespace models\core\Language\Lexicon;

use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\ModelCache;
use core\database\sql\query\Query;
use core\database\sql\Sql;
use core\database\sql\Table;

/**
 * @property string $rule
 */

#[Grid]
#[Table('core_lexicon_rule')]
#[Database(App::DATABASE)]
class Rule extends Model {
    use ModelCache;

    public static function fromRule(string $rule, bool $doCreate = false): ?static {
        static::modelCache_loadAll(fn(Rule $x) => $x->rule);
        $instance = static::modelCache_get($rule);

        if (is_null($instance) && $doCreate) {
            $instance = new static();
            $instance->set(['rule' => $rule]);
            $instance->save();
            static::modelCache_set($rule, $instance);
        }

        return $instance;
    }

    public static function forTranslation(Translation $translation): array {
        return self::forTranslationRaw($translation->getId());
    }

    public static function forTranslationRaw(int $translationId): array {
        static::modelCache_loadAll(fn(Rule $x) => $x->rule);

        $description = static::getDescription();
        $tr = $description->connection->getDriver()->escapeTable(Translation::TABLE_TRANSLATION_X_RULE);

        $ruleIds = Sql::select($tr)
            ->projection('id_rule')
            ->where(Query::infer("id_translation = ?", $translationId))
            ->fetchAll($description->connection);

        $rules = [];

        foreach ($ruleIds as $ruleId) {
            $rules[] = static::modelCache_fromId($ruleId);
        }

        return $rules;
    }



    #[Column('id_rule', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    protected string $rule;



    public function match(string $value): bool {
        return preg_match($this->rule, $value);
    }
}