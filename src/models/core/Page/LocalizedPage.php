<?php

namespace models\core\Page;

use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\forms\description\TextField;

#[Table('core_page_localization')]
#[Database(App::DATABASE)]
class LocalizedPage extends Model {
    public static function fromPageRaw(int $pageId, int $languageId): ?static {
        return self::first(
            where: Query::infer('id_page = ? AND id_language = ?', $pageId, $languageId)
        );
    }



    #[Column('id_localized_page', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[Column('id_page', type: Column::TYPE_INTEGER)]
    protected int $pageId;

    #[Column('id_language', type: Column::TYPE_INTEGER)]
    protected int $languageId;

    #[Column('id_slug', type: Column::TYPE_INTEGER)]
    protected int $slugId;

    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    protected string $title;
}