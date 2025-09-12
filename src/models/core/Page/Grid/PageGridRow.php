<?php

namespace models\core\Page\Grid;

use components\core\Admin\Nexus\NexusProxy;
use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use components\layout\Grid\description\GridDescription;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\query\SelectQuery;
use core\database\sql\Sql;
use core\database\sql\Table;
use models\core\Language\Language;
use models\core\Navigation\Slug;
use models\core\Page\LocalizedPage;
use models\core\Page\Page;
use models\core\Page\PageTemplate;
use models\extensions\Enable\Enable;
use models\extensions\Enable\EnableExtension;

/**
 * @property string $title
 * @property string $template
 */

#[Grid]
#[Table]
#[Database(App::DATABASE)]
class PageGridRow extends Model implements Enable {
    use EnableExtension;

    public static function getGridDescription(): GridDescription {
        $grid = GridDescription::extract(static::class);

        return new GridDescription(
            $grid->getColumns(),
            new PageGridLoader(),
            new NexusProxy()
        );
    }

    public static function children(Language $language, ?int $parentId = null): array {
        return self::childrenRaw($language->getId(), $parentId);
    }

    public static function childrenQuery(int $languageId, ?int $parentId = null): SelectQuery {
        $slug = Slug::getDescription();
        $id_parent = $slug->getEscapedColumn('id_parent');

        $localizedPage = LocalizedPage::getDescription();
        $id_language = $localizedPage->getEscapedColumn('id_language');

        $pageTemplate = PageTemplate::getDescription();

        return Sql::select($localizedPage->getEscapedTable())
            ->projection($localizedPage->getEscapedColumn('id_page'))
            ->projection($localizedPage->getEscapedColumn('title'))
            ->projection($pageTemplate->getEscapedColumn('name'))
            ->join(
                $slug->getEscapedTable(),
                is_null($parentId)
                    ? Query::infer("$id_parent IS NULL AND $id_language = ?", $languageId)
                    : Query::infer("$id_parent = ? AND $id_language = ?", $parentId, $languageId)
            )
            ->naturalJoin(Page::getDescription()->getEscapedTable())
            ->naturalJoin($pageTemplate->getEscapedTable());
    }

    public static function childrenRaw(int $languageId, ?int $parentId = null): array {
        $description = self::getDescription();
        return static::fromRecords(
            self::childrenQuery($languageId, $parentId)
                ->fetchAll($description->connection)
        );
    }



    #[Column('id_page', Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[GridColumn]
    #[Column(Column::TYPE_STRING)]
    protected string $title;

    #[GridColumn]
    #[Column('name', Column::TYPE_STRING)]
    protected string $template;
}