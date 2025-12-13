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
use core\forms\description\TextArea;

/**
 * @property int $pageId
 * @property string $description
 */

#[Grid]
#[Database(App::DATABASE)]
#[Table('core_ai_page')]
class AiPage extends Model {
    public static function fromPage(Page $page): ?static {
        return static::fromPageRaw($page->getId());
    }

    public static function fromPageRaw(int $pageId): ?static {
        return static::first(
            where: Query::infer('id_page = ?', $pageId)
        );
    }



    #[Column('id_ai_page', Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[Column('id_page', Column::TYPE_INTEGER)]
    protected int $pageId;

    #[GridColumn]
    #[TextArea]
    #[Column(type: Column::TYPE_STRING)]
    protected string $description;



    protected ?Page $page;



    public function getPage(): ?Page {
        if (!isset($this->page)) {
            $this->page = Page::fromId($this->pageId);
        }

        return $this->page;
    }
}