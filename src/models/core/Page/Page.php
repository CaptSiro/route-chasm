<?php

namespace models\core\Page;

use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\Page\AdminPageEditor;
use core\App;
use core\data\DataItem;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\DatabaseAction;
use core\database\sql\Model;
use core\database\sql\ModelDescription;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\forms\description\DateTime;
use core\forms\description\select\Select;
use core\navigation\Destination;
use core\navigation\Navigator;
use core\pages\PageLinkCreator;
use core\pages\Pages;
use core\pages\PageTemplate;
use core\route\Path;
use core\route\Route;
use core\route\RouteSegment;
use core\RouteChasmEnvironment;
use core\utils\Arrays;
use core\utils\Strings;
use http\Exception\RuntimeException;
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
class Page extends Model implements Destination {
    public const DATA_NAMESPACE = 'page';



    public static function getNexus(): AdminNexus {
        return (new AdminNexus(
            ModelDescription::extract(static::class),
            new AdminPageEditor(new PageEditorBehavior()),
            PageGridRow::getGridDescription(),
            title: '&nbsp;'
        ))->setLinkCreator(new PageLinkCreator());
    }

    /**
     * @return array<Page>
     */
    public static function children(?Page $parent = null): array {
        return self::childrenRaw($parent?->getId());
    }

    public static function childrenRaw(?int $parentId = null): array {
        return self::all(where: is_null($parentId)
            ? Query::static('id_page_parent IS NULL')
            : Query::infer('id_page_parent = ?', $parentId));
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
    /** @var array<LocalizedPage> */
    protected array $localizations;
    protected PageStatus $status;
    protected PageTemplateRecord $template;
    protected array $children;



    public function delete(): DatabaseAction {
        $template = $this->getTemplate();
        if (!is_null($error = $template->delete($this))) {
            App::getInstance()->getResponse()->renderRoot($error);
        }

        foreach ($this->getLocalizations() as $localization) {
            $localization->delete();
        }

        return parent::delete();
    }



    public function getParent(): ?Page {
        if (!isset($this->parent)) {
            $this->parent = self::fromId($this->parentId);
        }

        return $this->parent;
    }

    /**
     * @return array<Page>
     */
    public function getParents(): array {
        $ret = [];
        $current = $this;

        while (true) {
            if (is_null($parent = $current->getParent())) {
                break;
            }

            $ret[] = $current = $parent;
        }

        return array_reverse($ret);
    }

    public function setParent(?Page $parent): void {
        $this->set(['parentId' => $parent?->getId()]);
        $this->parent = $parent;
    }

    public function getLocalization(Language $language): ?LocalizedPage {
        return $this->getLocalizations()[$language->getId()] ?? null;
    }

    public function getLocalizationOrDefault(Language $language): ?LocalizedPage {
        return $this->getLocalization($language)
            ?? $this->getLocalization(Language::getDefault());
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

    public function getTemplateRecord(): ?PageTemplateRecord {
        if (!isset($this->template)) {
            $this->template = PageTemplateRecord::fromId($this->templateId);
        }

        return $this->template;
    }

    public function getTemplate(): ?PageTemplate {
        if (is_null($record = $this->getTemplateRecord())) {
            return null;
        }

        return Pages::getTemplate($record->getId());
    }

    /**
     * @return array<Page>
     */
    public function getChildren(): array {
        if (!isset($this->children)) {
            $this->children = self::childrenRaw($this->getId());
        }

        return $this->children;
    }

    public function get(string $item = ''): DataItem {
        $file = Strings::lpad('0', (string) $this->getId(), RouteChasmEnvironment::ID_DIGITS);
        if (!empty($item)) {
            $file .= '_'. $item;
        }

        return new DataItem(
            self::DATA_NAMESPACE,
            $file
        );
    }

    public function getPathToSelf(string $alias): Path {
        if (is_null($mount = Navigator::locate($alias))) {
            throw new RuntimeException("Alias '$alias' is not mounted properly. Use Navigator::route to create new mounting point");
        }

        $route = $mount->getMountingPoint();
        $language = App::getInstance()
            ->getRequest()
            ->getLanguage();

        foreach ($this->getParents() as $page) {
            if (is_null($localization = $page->getLocalizationOrDefault($language))) {
                $id = $page->getId();
                throw new RuntimeException("Page($id) does not have title for current or default language");
            }

            $route->add(RouteSegment::static($localization->getSlug()->slug));
        }

        if (is_null($localization = $this->getLocalization($language))) {
            $id = $this->getId();
            throw new RuntimeException("Page($id) does not have title for current or default language");
        }

        $route->add(RouteSegment::static($localization->getSlug()->slug));
        return $mount->transform($route);
    }
}