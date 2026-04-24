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

#[Grid]
#[Database(App::DATABASE)]
#[Table('core_ai_page')]
class AiPage extends Model {
    public static function fromPage(Page $page, bool $create = false): ?static {
        if (!is_null($ret = static::fromPageRaw($page->id))) {
            return $ret;
        }

        if (!$create) {
            return null;
        }

        $model = new AiPage();
        $model->pageId = $page->id;
        $model->save();

        return $model;
    }

    public static function fromPageRaw(int $pageId): ?static {
        return static::first(
            where: Query::infer('id_page = ?', $pageId)
        );
    }



    #[Column('id_ai_page', Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[Column('id_page', Column::TYPE_INTEGER)]
    public int $pageId;

    #[GridColumn]
    #[TextArea(rows: 10)]
    #[Column(type: Column::TYPE_STRING)]
    public string $prompt = '';



    protected ?Page $page;



    public function getPage(): ?Page {
        if (!isset($this->page)) {
            $this->page = Page::fromId($this->pageId);
        }

        return $this->page;
    }
}