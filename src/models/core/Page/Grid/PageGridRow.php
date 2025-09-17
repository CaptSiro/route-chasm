<?php

namespace models\core\Page\Grid;

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
use models\core\Page\behavior\PageProxy;
use models\core\Page\LocalizedPage;
use models\core\Page\Page;
use models\core\Page\PageTemplateRecord;

/**
 * @property string $title
 * @property string $template
 */

#[Grid]
#[Table]
#[Database(App::DATABASE)]
class PageGridRow extends Model {
    public static function getGridDescription(): GridDescription {
        $grid = GridDescription::extract(static::class);

        return new GridDescription(
            $grid->getColumns(),
            new PageGridLoader(),
            new PageProxy()
        );
    }

    public static function children(Language $language, ?int $parentId = null): array {
        return self::childrenRaw($language->getId(), $parentId);
    }

    public static function childrenQuery(int $languageId, ?int $parentId = null): SelectQuery {
        $page = Page::getDescription();
        $id_parent = $page->getEscapedColumn('id_page_parent');

        $localizedPage = LocalizedPage::getDescription();
        $id_language = $localizedPage->getEscapedColumn('id_language');

        $pageTemplate = PageTemplateRecord::getDescription();

        return Sql::select($localizedPage->getEscapedTable())
            ->projection($localizedPage->getEscapedColumn('id_page'))
            ->projection($localizedPage->getEscapedColumn('title'))
            ->projection($pageTemplate->getEscapedColumn('name'))
            ->naturalJoin($page->getEscapedTable())
            ->naturalJoin($pageTemplate->getEscapedTable())
            ->where(is_null($parentId)
                ? Query::infer("$id_parent IS NULL AND $id_language = ?", $languageId)
                : Query::infer("$id_parent = ? AND $id_language = ?", $parentId, $languageId)
            );
    }

    public static function childrenRaw(int $languageId, ?int $parentId = null): array {
        $description = self::getDescription();
        return static::fromRecords(
            self::childrenQuery($languageId, $parentId)
                ->fetchAll($description->connection)
        );
    }



    #[Column('id_page', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    protected string $title;

    #[GridColumn]
    #[Column('name', type: Column::TYPE_STRING)]
    protected string $template;
}