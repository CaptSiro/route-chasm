<?php

namespace models\core\Page;

use components\core\Admin\Nexus\AdminNexus;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\ModelDescription;
use core\database\sql\Table;
use core\forms\description\FormDescription;
use core\forms\description\TextField;
use core\pages\PageLinkCreator;
use models\core\Language\Language;
use models\core\Page\Grid\PageGridRow;
use models\extensions\Enable\Enable;
use models\extensions\Enable\EnableExtension;

#[Table('core_page')]
#[Database(App::DATABASE)]
class Page extends Model implements Enable {
    public static function getNexus(): AdminNexus {
        return (new AdminNexus(
            ModelDescription::extract(static::class),
            FormDescription::getEditor(static::class),
            PageGridRow::getGridDescription()
        ))->setLinkCreator(new PageLinkCreator());
    }

    public static function localized(int $pageId, Language $language): ?static {
        $localization = LocalizedPage::fromPageRaw($pageId, $language->getId());
        if (is_null($localization)) {
            return null;
        }

        $page = static::fromId($pageId);
        if (is_null($page)) {
            return null;
        }

        $page->localization = $localization;

        return $page;
    }



    use EnableExtension;

    #[Column('id_page', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[Column('id_page_template', type: Column::TYPE_INTEGER)]
    protected int $templateId;

    #[Column('id_page_status', type: Column::TYPE_INTEGER)]
    protected int $statusId;

    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    protected string $created;

    #[Column(type: Column::TYPE_STRING)]
    protected string $updated;

    #[Column(type: Column::TYPE_STRING)]
    protected string $publish;

    #[Column(type: Column::TYPE_STRING)]
    protected string $remove;



    protected LocalizedPage $localization;
    protected PageStatus $status;



    public function getLocalization(): ?LocalizedPage {
        if (!isset($this->localization)) {
            return null;
        }

        return $this->localization;
    }

    public function getStatus(): ?PageStatus {
        if (!isset($this->status)) {
            $this->status = PageStatus::fromId($this->statusId);
        }

        return $this->status;
    }
}