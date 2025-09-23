<?php

namespace models\core\Page;

use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\forms\description\TextArea;
use core\forms\description\TextField;

/**
 * @property string $description
 * @property string $keywords
 * @property string $ogTitle
 * @property string $ogDescription
 */

#[Table('ext_page_meta')]
#[Database(App::DATABASE)]
class PageMeta extends Model {
    public static function fromLocalizedPage(LocalizedPage $page): ?static {
        return static::first(
            where: Query::infer('id_localized_page = ?', $page->getId())
        );
    }



    #[Column('id_page_meta', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[Column('id_localized_page', type: Column::TYPE_INTEGER)]
    protected int $localizedPageId;

    #[TextArea]
    #[Column(type: Column::TYPE_STRING)]
    protected string $description;

    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    protected string $keywords;

    #[TextField('Open Graph Title')]
    #[Column('og_title', type: Column::TYPE_STRING)]
    protected string $ogTitle;

    #[TextArea('Open Graph Description')]
    #[Column('og_description', type: Column::TYPE_STRING)]
    protected string $ogDescription;

//    todo
//      #[Column('og_image', type: Column::TYPE_IMAGE)]
//      protected string $image;

    protected LocalizedPage $localization;



    public function setLocalization(LocalizedPage $localization): void {
        $this->set(['localizedPageId' => $localization->getId()]);
        $this->localization = $localization;
    }
}