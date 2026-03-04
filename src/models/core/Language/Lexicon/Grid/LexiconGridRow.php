<?php

namespace models\core\Language\Lexicon\Grid;

use components\core\Admin\Nexus\NexusProxy;
use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use components\layout\Grid\description\GridDescription;
use components\layout\Grid\Loader\ModelGridLoader;
use core\App;
use core\database\sql\Column;
use core\database\sql\Connection;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\ModelFactory;
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
            new LexiconGridLoader(portionSize: ModelGridLoader::getPortionSizeSetting()),
            proxy: new NexusProxy()
        );
    }

    public static function phrases(): array {
        return self::phrasesRaw();
    }

    protected static function phrasesBaseQuery(Connection $connection): SelectQuery {
        $translation = Translation::getDescription();

        $inner = Sql::select($translation->getEscapedTable())
            ->projection($id_phrase = $translation->getEscapedColumn('id_phrase'))
            ->projection('COUNT(*) AS translations')
            ->group($id_phrase);

        $lexicon = Phrase::getDescription();
        $lexicon_id = $lexicon->getEscapedColumn('id_phrase');

        return Sql::select($lexicon->getEscapedTable())
            ->naturalJoin(LexiconGroup::getDescription()->getEscapedTable())
            ->leftJoin(
                '(' .$inner->toQuery($connection). ') AS translation_count',
                Query::static("translation_count.id_phrase = $lexicon_id")
            );
    }

    public static function phrasesCountQuery(Connection $connection): SelectQuery {
        return self::phrasesBaseQuery($connection)
            ->projection(ModelFactory::PROJECTION_COUNT);
    }

    public static function phrasesCount(): int {
        $connection = self::getDescription()->getConnection();
        return ModelFactory::countExecuteConnection(
            self::phrasesCountQuery($connection),
            $connection
        );
    }

    public static function phrasesQuery(Connection $connection): SelectQuery {
        $lexicon = Phrase::getDescription();
        $group = LexiconGroup::getDescription();

        return self::phrasesBaseQuery($connection)
            ->projection($lexicon_id = $lexicon->getEscapedColumn('id_phrase'))
            ->projection($group->getEscapedColumn('name'))
            ->projection($lexicon->getEscapedColumn('default'))
            ->projection('translations');
    }

    public static function phrasesRaw(): array {
        $description = self::getDescription();
        return static::fromRecords(
            self::phrasesQuery($description->getConnection())
                ->fetchAll($description->getConnection())
        );
    }



    #[Column('id_phrase', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[GridColumn]
    #[Column('name', type: Column::TYPE_STRING)]
    public string $group;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    public string $default;

    #[GridColumn]
    #[Column(type: Column::TYPE_INTEGER)]
    public int $translations = 0;



    // Model
    public function getHumanIdentifier(): string {
        return $this->default;
    }

    public function isDeletable(): bool {
        return false;
    }
}