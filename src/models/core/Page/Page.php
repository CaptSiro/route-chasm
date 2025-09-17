<?php

namespace models\core\Page;

use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\Page\AdminPageEditor;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\ModelDescription;
use core\database\sql\Table;
use core\forms\description\DateTime;
use core\forms\description\select\Select;
use core\pages\PageLinkCreator;
use core\utils\Arrays;
use models\core\Language\Language;
use models\core\Page\behavior\PageEditorBehavior;
use models\core\Page\Grid\PageGridRow;
use models\extensions\Name\NameValues;

/**
 * @property int $templateId
 * @property int $statusId
 * @property string $created
 * @property string $updated
 * @property string $publish
 * @property string $remove
 */

#[Table('core_page')]
#[Database(App::DATABASE)]
class Page extends Model {
    public static function getNexus(): AdminNexus {
        return (new AdminNexus(
            ModelDescription::extract(static::class),
            new AdminPageEditor(new PageEditorBehavior()),
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



    #[Column('id_page', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[Column('id_page_parent', type: Column::TYPE_INTEGER, nullable: true)]
    protected ?int $parentId;

    #[Select(new NameValues(PageTemplateRecord::class), 'Template')]
    #[Column('id_page_template', type: Column::TYPE_INTEGER)]
    protected int $templateId;

    #[Select(new NameValues(PageStatus::class), 'Status', PageStatus::ID_DRAFT)]
    #[Column('id_page_status', type: Column::TYPE_INTEGER)]
    protected int $statusId;

    #[Column(type: Column::TYPE_STRING)]
    protected string $created;

    #[Column(type: Column::TYPE_STRING)]
    protected string $updated;

    #[DateTime]
    #[Column(type: Column::TYPE_STRING, nullable: true)]
    protected ?string $publish;

    #[DateTime]
    #[Column(type: Column::TYPE_STRING, nullable: true)]
    protected ?string $remove;



    protected ?Page $parent;
    protected LocalizedPage $localization;
    /** @var array<LocalizedPage> */
    protected array $localizations;
    protected PageStatus $status;
    protected PageTemplateRecord $template;



    public function getParent(): ?Page {
        if (!isset($this->parent)) {
            $this->parent = self::fromId($this->parentId);
        }

        return $this->parent;
    }

    public function setParent(?Page $parent): void {
        $this->set(['parentId' => $parent?->getId()]);
        $this->parent = $parent;
    }

    public function getLocalization(): ?LocalizedPage {
        if (!isset($this->localization)) {
            return null;
        }

        return $this->localization;
    }

    /**
     * @return array<int, LocalizedPage>
     */
    public function getLocalizations(): array {
        if (is_null($id = $this->getId())) {
            return [];
        }

        if (!isset($this->localizations)) {
            $this->localizations = Arrays::changeKeys(
                LocalizedPage::forPageRaw($id),
                fn(LocalizedPage $x) => $x->languageId
            );
        }

        return $this->localizations;
    }

    public function getStatus(): ?PageStatus {
        if (!isset($this->status)) {
            $this->status = PageStatus::fromId($this->statusId);
        }

        return $this->status;
    }

    public function getTemplate(): ?PageTemplateRecord {
        if (!isset($this->template)) {
            $this->template = PageTemplateRecord::fromId($this->templateId);
        }

        return $this->template;
    }
}