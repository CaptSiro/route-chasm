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

#[Table('ext_page_meta')]
#[Database(App::DATABASE)]
class PageMeta extends Model {
    public static function fromLocalization(PageLocalization $localization, bool $create = false): ?static {
        $meta = static::first(
            where: Query::infer('id_localized_page = ?', $localization->id)
        );

        if (is_null($meta) && $create) {
            $meta = new static();
            $meta->setLocalization($localization);
        }

        return $meta;
    }



    #[Column('id_page_meta', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[Column('id_localized_page', type: Column::TYPE_INTEGER)]
    public int $localizationId;

    #[TextArea]
    #[Column(type: Column::TYPE_STRING)]
    public string $description;

    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    public string $keywords;

    #[TextField('Open Graph Title')]
    #[Column('og_title', type: Column::TYPE_STRING)]
    public string $ogTitle;

    #[TextArea('Open Graph Description')]
    #[Column('og_description', type: Column::TYPE_STRING)]
    public string $ogDescription;

    protected PageLocalization $localization;



    public function setLocalization(PageLocalization $localization): void {
        $this->localizationId = $localization->id;
        $this->localization = $localization;
    }
}