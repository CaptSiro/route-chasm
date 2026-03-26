<?php

namespace models\core\Page\Grid;

use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use components\layout\Grid\description\GridDescription;
use components\layout\Grid\Loader\ModelGridLoader;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\ModelFactory;
use core\database\sql\query\Query;
use core\database\sql\query\SelectQuery;
use core\database\sql\Sql;
use core\database\sql\Table;
use models\core\Language\Language;
use models\core\Page\behavior\PageProxy;
use models\core\Page\PageLocalization;
use models\core\Page\Page;
use models\core\Page\PageStatus;
use models\core\Page\PageTemplateRecord;
use const models\extensions\Priority\COLUMN_PRIORITY;

#[Grid]
#[Table]
#[Database(App::DATABASE)]
class PageGridRow extends Model {
    public static function getGridDescription(): GridDescription {
        $grid = GridDescription::extract(static::class);

        return new GridDescription(
            $grid->getColumns(),
            new PageGridLoader(portionSize: ModelGridLoader::getPortionSizeSetting()),
            proxy: new PageProxy()
        );
    }

    public static function children(Language $language, ?int $parentId = null): array {
        return self::childrenRaw($language->id, $parentId);
    }

    public static function childrenBaseQuery(int $languageId, ?int $parentId = null): SelectQuery {
        $page = Page::getDescription();
        $id_parent = $page->getEscapedColumn('id_page_parent');
        $page_id_page_status = $page->getEscapedColumn('id_page_status');

        $localizedPage = PageLocalization::getDescription();
        $id_language = $localizedPage->getEscapedColumn('id_language');

        $pageTemplate = PageTemplateRecord::getDescription();
        $pageStatus = PageStatus::getDescription();
        $status = $pageStatus->getEscapedTable();
        $status_id_page_status = $pageStatus->getEscapedColumn('id_page_status');

        return Sql::select($localizedPage->getEscapedTable())
            ->naturalJoin($page->getEscapedTable())
            ->naturalJoin($pageTemplate->getEscapedTable())
            ->join($status, Query::static("$page_id_page_status = $status_id_page_status"))
            ->where(is_null($parentId)
                ? Query::infer("$id_parent IS NULL AND $id_language = ?", $languageId)
                : Query::infer("$id_parent = ? AND $id_language = ?", $parentId, $languageId)
            );
    }

    public static function childrenQuery(int $languageId, ?int $parentId = null): SelectQuery {
        $page = Page::getDescription();
        $localizedPage = PageLocalization::getDescription();
        $pageTemplate = PageTemplateRecord::getDescription();
        $pageStatus = PageStatus::getDescription();

        $priority = $page->getEscapedColumn(COLUMN_PRIORITY);

        return self::childrenBaseQuery($languageId, $parentId)
            ->projection($priority)
            ->projection($localizedPage->getEscapedColumn('id_page'))
            ->projection($localizedPage->getEscapedColumn('title'))
            ->projection($pageTemplate->getEscapedColumn('name') .' AS template')
            ->projection($pageStatus->getEscapedColumn('name') .' AS status')
            ->order($priority);
    }

    public static function childrenCountQuery(int $languageId, ?int $parentId = null): SelectQuery {
        return self::childrenBaseQuery($languageId, $parentId)
            ->projection(ModelFactory::PROJECTION_COUNT);
    }

    public static function phrasesCount(int $languageId, ?int $parentId = null): int {
        return ModelFactory::countExecuteConnection(
            self::childrenCountQuery($languageId, $parentId),
            self::getDescription()->getConnection()
        );
    }

    public static function childrenRaw(int $languageId, ?int $parentId = null): array {
        $description = self::getDescription();
        return static::fromRecords(
            self::childrenQuery($languageId, $parentId)
                ->fetchAll($description->getConnection())
        );
    }



    #[Column('id_page', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[GridColumn(template: '92px')]
    #[Column(type: Column::TYPE_STRING)]
    public string $priority;

    #[GridColumn(template: '128px')]
    #[Column(type: Column::TYPE_STRING)]
    public string $status;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    public string $title;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    public string $template;



    public function getHumanIdentifier(): string {
        return $this->title;
    }
}