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
use core\database\sql\Table;
use core\forms\description\TextField;

#[Grid]
#[Table('core_lexicon_group')]
#[Database(App::DATABASE)]
class LexiconGroup extends Model {
    use ModelCache;

    public static function fromName(string $name, bool $create = false): ?static {
        if (!is_null($ret = self::modelCache_get($name))) {
            return $ret;
        }

        return self::modelCache_set(
            $name,
            static::createConditionally(
                static::first(
                    where: Query::infer('name = ?', $name)
                ),
                ['name' => $name],
                $create
            )
        );
    }



    #[Column('id_lexicon_group', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[TextField]
    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    public string $name;

    private array $phrases;



    // Model
    public function getHumanIdentifier(): string {
        return $this->name;
    }



    /**
     * @return array<Phrase>
     */
    public function getPhrases(): array {
        if (isset($this->phrases)) {
            return $this->phrases;
        }

        return $this->phrases = Phrase::all(
            where: Query::infer('id_lexicon_group = ?', $this->id)
        );
    }

    public function getPhraseCount(): int {
        return Phrase::count(
            where: Query::infer('id_lexicon_group = ?', $this->id)
        );
    }
}