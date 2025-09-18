<?php

namespace models\core\Language\Lexicon\Grid;

use components\core\Admin\Nexus\NexusProxy;
use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use components\layout\Grid\description\GridDescription;
use core\App;
use core\database\sql\Column;
use core\database\sql\Connection;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\query\SelectQuery;
use core\database\sql\Sql;
use core\database\sql\Table;
use models\core\Language\Lexicon\LexiconGroup;
use models\core\Language\Lexicon\Phrase;
use models\core\Language\Lexicon\Translation;

#[Grid]
#[Table]
#[Database(App::DATABASE)]
class LexiconGridRow extends Model {
    public static function getGridDescription(): GridDescription {
        $grid = GridDescription::extract(static::class);

        return new GridDescription(
            $grid->getColumns(),
            new LexiconGridLoader(),
            new NexusProxy()
        );
    }

    public static function phrases(): array {
        return self::phrasesRaw();
    }

    public static function phrasesQuery(Connection $connection): SelectQuery {
        $translation = Translation::getDescription();

        $inner = Sql::select($translation->getEscapedTable())
            ->projection($id_phrase = $translation->getEscapedColumn('id_phrase'))
            ->projection('COUNT(*) AS translations')
            ->group($id_phrase);

        $lexicon = Phrase::getDescription();
        $group = LexiconGroup::getDescription();

        return Sql::select($lexicon->getEscapedTable())
            ->projection($lexicon_id = $lexicon->getEscapedColumn('id_phrase'))
            ->projection($group->getEscapedColumn('name'))
            ->projection($lexicon->getEscapedColumn('default'))
            ->projection('translations')
            ->naturalJoin($group->getEscapedTable())
            ->leftJoin(
                '(' .$inner->toQuery($connection). ') AS translation_count',
                Query::static("translation_count.id_phrase = $lexicon_id")
            );
    }

    public static function phrasesRaw(): array {
        $description = self::getDescription();
        return static::fromRecords(
            self::phrasesQuery($description->connection)
                ->fetchAll($description->connection)
        );
    }



    #[Column('id_phrase', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[GridColumn]
    #[Column('name', type: Column::TYPE_STRING)]
    protected string $group;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    protected string $default;

    #[GridColumn]
    #[Column(type: Column::TYPE_INTEGER)]
    protected int $translations = 0;



    public function isDeletable(): bool {
        return false;
    }
}