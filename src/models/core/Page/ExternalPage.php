<?php

namespace models\core\Page;

use components\layout\Grid\description\Grid;
use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\forms\description\TextField;

#[Grid]
#[Database(App::DATABASE)]
#[Table('core_external_page')]
class ExternalPage extends Model {
    public static function fromPage(Page $page): ?static {
        return static::fromPageRaw($page->id);
    }

    public static function fromPageRaw(int $pageId): ?static {
        return static::first(
            where: Query::infer('id_page = ?', $pageId)
        );
    }



    #[Column('id_external_page', Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[Column('id_page', Column::TYPE_INTEGER)]
    public int $pageId;

    #[GridColumn]
    #[TextField('URL')]
    #[Column(type: Column::TYPE_STRING)]
    public string $url = '';



    protected ?Page $page;



    public function getPage(): ?Page {
        if (!isset($this->page)) {
            $this->page = Page::fromId($this->pageId);
        }

        return $this->page;
    }
}