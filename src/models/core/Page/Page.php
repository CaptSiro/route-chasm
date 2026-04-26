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
use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\query\SelectQuery;
use core\database\sql\query\UpdateQuery;
use core\database\sql\SideEffect;
use core\database\sql\Sql;
use core\database\sql\Table;
use core\forms\description\DateTime;
use core\forms\description\select\Select;
use core\navigation\Destination;
use core\navigation\Navigator;
use core\pages\PageFactory;
use core\pages\PageLinkCreator;
use core\pages\Pages;
use core\pages\PageTemplate;
use core\route\Path;
use core\route\RouteSegment;
use core\RouteChasmEnvironment;
use core\url\Url;
use core\utils\Arrays;
use core\view\View;
use DateTime as DateTimeObject;
use models\core\fs\Shortcut;
use models\core\Language\Language;
use models\core\Menu;
use models\core\Navigation\Slug;
use models\core\Page\behavior\PageEditorBehavior;
use models\core\Page\Grid\PageGridRow;
use models\core\Privilege\Privilege;
use models\core\User\User;
use models\core\UserResource;
use models\extensions\Name\NameValues;
use models\extensions\Priority\Priority;
use models\extensions\Priority\PriorityExtension;
use models\extensions\Priority\PriorityTrait;
use RuntimeException;

#[Table('core_page')]
#[Database(App::DATABASE)]
class Page extends Model implements Destination, Priority {
    public const DATA_NAMESPACE = 'page';

    public const TABLE_RELATED_PAGES = 'core_related_pages';



    public static function getNexus(): AdminNexus {
        return (new AdminNexus(
            ModelDescription::extract(static::class),
            new AdminPageEditor(new PageEditorBehavior()),
            PageGridRow::getGridDescription(),
            title: '&nbsp;'
        ))
            ->setLinkCreator(new PageLinkCreator())
            ->addExtension(
                new PriorityExtension(function (UpdateQuery $update, Model $model) {
                    if ($model instanceof Page) {
                        $update->where(Page::childrenQuery($model->parentId));
                    }
                })
            );
    }

    public static function publishedQuery(): Query {
        $description = static::getDescription();
        $publish = $description->getEscapedColumn('publish');
        $remove = $description->getEscapedColumn('remove');

        return Query::static("($publish IS NULL OR NOW() >= $publish) AND ($remove IS NULL OR NOW() <= $remove)");
    }

    public static function isStatusQuery(int $statusId): Query {
        return Query::infer('id_page_status = ?', $statusId);
    }

    /**
     * @param int $statusId
     * @param int|null $limit
     * @return array<Page>
     */
    public static function lastUpdated(
        int $statusId = PageStatus::ID_PUBLIC, ?int $limit = RouteChasmEnvironment::LIMIT_LAST_UPDATED
    ): array {
        $factory = static::getDescription()->getFactory();

        $sql = $factory->allQuery()
            ->where(self::publishedQuery())
            ->where(self::isStatusQuery($statusId))
            ->order('updated', 'DESC');

        if (!is_null($limit)) {
            $sql->limit($limit);
        }

        return $factory->allExecute($sql);
    }

    /**
     * @return array<Page>
     */
    public static function children(?Page $parent = null): array {
        return self::childrenRaw($parent?->id);
    }

    public static function childrenRaw(?int $parentId = null): array {
        return self::all(where: self::childrenQuery($parentId));
    }

    public static function countChildren(?Page $parent = null): int {
        return self::countChildrenRaw($parent?->id);
    }

    public static function countChildrenRaw(?int $parentId = null): int {
        return self::count(where: self::childrenQuery($parentId));
    }

    public static function childrenQuery(?int $parentId = null): Query {
        return is_null($parentId)
            ? Query::static('id_page_parent IS NULL')
            : Query::infer('id_page_parent = ?', $parentId);
    }

    /**
     * @return array<Page>
     */
    public static function related(Page $source): array {
        return self::childrenRaw($source->id);
    }

    public static function relatedRaw(int $sourceId): array {
        $description = static::getDescription();
        $driver = $description->getConnection()->getDriver();
        $factory = $description->getFactory();

        $id_page = $description->getEscapedColumn('id_page');

        $related = $driver->escapeTable('core_related_pages');
        $source = $driver->escapeColumn('id_source');
        $target = $driver->escapeColumn('id_target');

        return $factory->allExecute(
            $factory->allQuery()
                ->join($related, Query::infer("$related.$target = $id_page AND $related.$source = ?", $sourceId))
        );
    }

    public static function hasPageAccess(User $user, Privilege $privilege): bool {
        if ($user->isRoot()) {
            return true;
        }

        return $user->hasAccess(
            UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_PAGE),
            $privilege
        );
    }

    public static function searchFullTextQuery(string $query, ?int $languageId = null): SelectQuery {
        $page = static::getDescription();
        $localization = PageLocalization::getDescription();
        $title = $localization->getEscapedColumn('title');
        $meta = PageMeta::getDescription();
        $description = $meta->getEscapedColumn('description');

        $expression = "%$query%";

        $sql = Sql::select($page->getEscapedTable())
            ->distinct()
            ->naturalJoin($localization->getEscapedTable())
            ->naturalJoin($meta->getEscapedTable())
            ->where(static::publishedQuery())
            ->where(static::isStatusQuery(PageStatus::ID_PUBLIC))
            ->where(Query::infer("($title LIKE ? OR $description LIKE ?)", $expression, $expression));

        if (!is_null($languageId)) {
            $sql->where(Query::infer('id_language = ?', $languageId));
        }

        $page->projection($sql);

        return $sql;
    }

    /**
     * @param string $query
     * @param int|null $languageId
     * @return array<Page>
     */
    public static function searchFullText(string $query, ?int $languageId = null): array {
        $description = static::getDescription();

        return $description->getFactory()
            ->allExecute(static::searchFullTextQuery($query, $languageId));
    }



    #[Column('id_page', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[Column('id_page_parent', type: Column::TYPE_INTEGER, nullable: true)]
    public ?int $parentId;

    #[Select(new NameValues(PageTemplateRecord::class), 'Template')]
    #[Column('id_page_template', type: Column::TYPE_INTEGER)]
    public int $templateId;

    #[Select(new NameValues(PageStatus::class), 'Status', PageStatus::ID_DRAFT)]
    #[Column('id_page_status', type: Column::TYPE_INTEGER)]
    public int $statusId;

    #[Column(type: Column::TYPE_STRING, nullable: true)]
    public ?string $created;

    #[Column(type: Column::TYPE_STRING, nullable: true)]
    public ?string $updated;

    #[DateTime]
    #[Column(type: Column::TYPE_STRING, nullable: true)]
    public ?string $publish;

    #[DateTime]
    #[Column(type: Column::TYPE_STRING, nullable: true)]
    public ?string $remove;

    use PriorityTrait;



    protected ?Page $parent;
    /** @var array<PageLocalization> */
    protected array $localizations;
    protected PageStatus $status;
    protected PageTemplateRecord $template;
    /** @var array<Page> */
    protected array $children;
    /** @var array<Page> */
    protected array $related;



    // Model
    public function getHumanIdentifier(): string {
        return $this->getLocalizationOrDefault()->getHumanIdentifier();
    }

    public function save(): DatabaseAction|View {
        if ($this->isNewRecord()) {
            $this->priority = self::countChildren($this->getParent());
        }

        $result = parent::save();
        if ($result === DatabaseAction::INSERT) {
            if (!is_null($error = $this->getTemplate()?->create($this))) {
                return $error;
            }
        }

        return $result;
    }

    public function delete(): DatabaseAction {
        $template = $this->getTemplate();
        if (!is_null($error = $template->delete($this))) {
            App::getInstance()->getResponse()->renderRoot($error);
        }

        foreach ($this->getChildren() as $child) {
            $child->delete();
        }

        foreach ($this->getLocalizations() as $localization) {
            $localization->delete();
        }

        return parent::delete();
    }



    public function getParent(): ?Page {
        if (!isset($this->parent)) {
            $this->parent = self::fromId($this->parentId, cache: true);
        }

        return $this->parent;
    }

    /**
     * @return array<Page>
     */
    public function getParents(bool $addSelf = false): array {
        $ret = [];
        $current = $this;

        if ($addSelf) {
            $ret[] = $this;
        }

        while (true) {
            if (is_null($parent = $current->getParent())) {
                break;
            }

            $ret[] = $current = $parent;
        }

        return array_reverse($ret);
    }

    public function createPath(Language $language): Path {
        $segments = [];

        foreach ($this->getParents(true) as $node) {
            $segments[] = $node->getLocalizationOrDefault($language)?->title ?? '[No title]';
        }

        return new Path($segments);
    }

    public function setParent(?Page $parent): void {
        if (is_null($parent)) {
            $this->parentId = null;
        } else {
            $this->parentId = $parent->id;
        }

        $this->parent = $parent;
    }

    public function getParentSlugId(Language $language): ?int {
        if (is_null($parent = $this->getParent())) {
            return null;
        }

        $localization = $parent->getLocalization($language)
            ?? $parent->getLocalization(App::getDefaultLanguage());

        if (is_null($localization)) {
            // different value than null == no parent, not null == no language
            return null; // todo
        }

        return $localization->slugId;
    }

    public function isTitleAvailable(
        string $title, Language $language, int $navigationContextId
    ): bool {
        $localizations = $this->getLocalizations();
        $hasLocalization = isset($localizations[$language->id]);

        $slugParentId = !$hasLocalization
            ? $this->getParentSlugId($language)
            : $localizations[$language->id]->getSlug()->parentId;

        $slug = Slug::fromSlugRaw(
            $language->id,
            $navigationContextId,
            PageLocalization::createSlugLiteral($language, $title),
            $slugParentId
        );

        if (is_null($slug)) {
            return true;
        }

        // title remains same
        return $hasLocalization && $localizations[$language->id]->getSlug()->id === $slug->id;
    }

    public function createLocalization(
        string $title, Language $language, int $navigationContextId, array $metaProperties = []
    ): PageLocalization {
        $localization = new PageLocalization();

        $localization->title = $title;
        $localization->languageId = $language->id;
        $localization->setPage($this);

        $slugParentId = $this->getParentSlugId($language);
        $localization->setSlug(
            PageFactory::getInstance()->createSlug(
                $language->id,
                $navigationContextId,
                $localization->getSlugLiteral($language),
                $slugParentId,
                $this
            )
        );

        $localization->save();

        $meta = new PageMeta();
        $meta->set($metaProperties);
        $meta->setLocalization($localization);
        $meta->save();

        return $localization;
    }

    public function getLocalization(Language $language): ?PageLocalization {
        return $this->getLocalizations()[$language->id] ?? null;
    }

    public function getLocalizationOrDefault(?Language $language = null): ?PageLocalization {
        $language ??= App::getInstance()
            ->getRequest()
            ->getLanguage();

        return $this->getLocalization($language)
            ?? $this->getLocalization(Language::getDefault());
    }

    /**
     * @return array<int, PageLocalization>
     */
    public function getLocalizations(): array {
        if (!isset($this->id)) {
            return [];
        }

        if (!isset($this->localizations)) {
            $this->localizations = Arrays::changeKeys(
                PageLocalization::forPageRaw($this->id),
                fn(PageLocalization $x) => $x->languageId
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

    public function getReleaseDate(): string {
        return $this->publish ?? $this->updated;
    }

    public function isReleased(): bool {
        $releaseDate = new DateTimeObject($this->getReleaseDate());
        $endDate = !is_null($this->remove)
            ? new DateTimeObject($this->remove)
            : null;

        $now = new DateTimeObject();

        return $now >= $releaseDate && (is_null($endDate) || $now <= $endDate);
    }

    public function hasAccess(User $user, Privilege $privilege): bool {
        if ($user->isRoot()) {
            return true;
        }

        $status = $this->getStatus();
        if ($status->is(PageStatus::ID_DRAFT)) {
            return $user->hasAccess(
                UserResource::getSystemResource(RouteChasmEnvironment::USER_RESOURCE_PAGE),
                $privilege
            );
        }

        return $this->isReleased();
    }

    public function getTemplateRecord(bool $force = false): ?PageTemplateRecord {
        if (!isset($this->template) || $force) {
            $this->template = PageTemplateRecord::fromId($this->templateId);
        }

        return $this->template;
    }

    public function getTemplate(bool $force = false): ?PageTemplate {
        if (is_null($record = $this->getTemplateRecord($force))) {
            return null;
        }

        return Pages::getTemplate($record->id);
    }

    protected bool $hasChildren;

    public function hasChildren(): bool {
        if (isset($this->hasChildren)) {
            return $this->hasChildren;
        }

        return $this->hasChildren = static::count(Query::infer('id_parent = ?', $this->id)) !== 0;
    }

    /**
     * @return array<Page>
     */
    public function getChildren(): array {
        if (!isset($this->children)) {
            $this->children = self::childrenRaw($this->id);
        }

        return $this->children;
    }

    public function getChildrenCount(): int {
        return self::countChildrenRaw($this->id);
    }

    /**
     * @return array<Page>
     */
    public function getRelated(): array {
        if (!isset($this->related)) {
            $this->related = self::relatedRaw($this->id);
        }

        return $this->related;
    }

    public function clearRelated(): SideEffect {
        $description = static::getDescription();
        $driver = $description->getConnection()->getDriver();

        $related = $driver->escapeTable(self::TABLE_RELATED_PAGES);
        $source = $driver->escapeColumn('id_source');

        return Sql::delete(self::TABLE_RELATED_PAGES)
            ->where(Query::infer("$related.$source = ?", $this->id))
            ->run($description->getConnection());
    }

    public function addRelatedRaw(array $targets): SideEffect {
        $description = static::getDescription();
        $driver = $description->getConnection()->getDriver();

        $related = $driver->escapeTable(self::TABLE_RELATED_PAGES);

        $sql = Sql::insert(self::TABLE_RELATED_PAGES);
        $sql->columns(['id_source', 'id_target']);

        $sourceId = Parameter::infer($this->id);

        foreach ($targets as $targetId) {
            $sql->value([
                $sourceId,
                new Parameter($targetId, $sourceId->getType()),
            ]);
        }

        return $sql->run($description->getConnection());
    }

    public function get(string $item = ''): DataItem {
        return $this->getDataItem(self::DATA_NAMESPACE, $item);
    }

    public function getCoverImageName(): string {
        return $this->getMachineIdentifier('cover-image');
    }

    public function getCoverImage(): ?Shortcut {
        return Shortcut::fromName($this->getCoverImageName());
    }

    /**
     * @return array<Menu>
     */
    public function in(): array {
        return Menu::forPage($this);
    }



    protected function getPathToSelfUsingLanguage(string $alias, Language $language): Path {
        if (is_null($mount = Navigator::locate($alias))) {
            throw new RuntimeException("Alias '$alias' is not mounted properly. Use Navigator::route to create new mounting point");
        }

        $route = $mount->getMountingPoint();

        foreach ($this->getParents() as $page) {
            if (is_null($localization = $page->getLocalizationOrDefault($language))) {
                throw new RuntimeException($page->getMachineIdentifier() . " does not have title for current or default language");
            }

            $route->add(RouteSegment::static($localization->getSlug()->slug));
        }

        if (is_null($localization = $this->getLocalizationOrDefault($language))) {
            throw new RuntimeException($this->getMachineIdentifier() . " does not have title for current or default language");
        }

        $route->add(RouteSegment::static($localization->getSlug()->slug));
        return $mount->transform($route);
    }

    // Destination
    public function getPathToSelf(string $alias): Path {
        $language = App::getInstance()
            ->getRequest()
            ->getLanguage();

        return $this->getPathToSelfUsingLanguage($alias, $language);
    }

    public function getUrlToModel(string $navigatorMountAlias, ?Language $language = null): Url {
        $request = App::getInstance()->getRequest();
        $language ??= $request->getLanguage();
        $path = $this->getPathToSelfUsingLanguage($navigatorMountAlias, $language);
        $ret = $request
            ->getDomain()
            ->createUrl($path);

        $ret->loadTransitiveQueries($request->getUrl()->getQuery());
        $ret->setQueryArgument(RouteChasmEnvironment::QUERY_LANGUAGE, $language->code);
        $ret->getQuery()->remove(RouteChasmEnvironment::QUERY_LANGUAGE_LONG);
        return $ret;
    }

    public function getUrl(?Language $language = null): Url {
        return $this->getUrlToModel(RouteChasmEnvironment::MOUNT_DEFAULT_CONTEXT, $language);
    }
}