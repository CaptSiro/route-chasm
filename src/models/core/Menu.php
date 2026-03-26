<?php

namespace models\core;

use components\layout\Grid\description\Grid;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\query\SelectQuery;
use core\database\sql\Sql;
use core\database\sql\Table;
use models\core\Page\Page;
use models\core\Page\PageStatus;
use models\core\Privilege\Privilege;
use models\core\User\User;
use models\extensions\Name\CachedNameExtension;
use models\extensions\Name\Name;
use const models\extensions\Priority\COLUMN_PRIORITY;

#[Grid]
#[Database(App::DATABASE)]
#[Table('core_menu')]
class Menu extends Model implements Name {
    use CachedNameExtension;

    public const TABLE_MENU_X_PAGES = 'core_menu_x_pages';

    public const NAME_HEADER = 'Header';
    public const NAME_HEADER_DOCS = 'Header (Docs)';
    public const NAME_FOOTER = 'Footer';
    public const NAME_LEGAL = 'Legal';



    /**
     * @param Page $page
     * @return array<static>
     */
    public static function forPage(Page $page): array {
        $factory = static::getDescription()->getFactory();

        $sql = $factory
            ->allQuery(where: Query::infer('id_page = ?', $page->id))
            ->naturalJoin(self::TABLE_MENU_X_PAGES);

        return $factory->allExecute($sql);
    }



    #[Column('id_menu', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;



    protected array $pages;
    protected array $releasedPages;



    protected function getPagesQuery(): SelectQuery {
        $description = Page::getDescription();
        $factory = $description->getFactory();
        return $factory
            ->allQuery(where: Query::infer('id_menu = ?', $this->id))
            ->naturalJoin(self::TABLE_MENU_X_PAGES)
            ->order(COLUMN_PRIORITY);
    }

    /**
     * @return array<Page>
     */
    public function getPages(): array {
        if (isset($this->pages)) {
            return $this->pages;
        }

        return $this->pages = Page::getDescription()
            ->getFactory()
            ->allExecute($this->getPagesQuery());
    }

    /**
     * @return array<Page>
     */
    public function getReleasedPages(): array {
        if (isset($this->releasedPages)) {
            return $this->releasedPages;
        }

        $sql = $this->getPagesQuery();

        $user = User::fromRequest(App::getInstance()->getRequest());
        if (!Page::hasPageAccess($user, Privilege::fromName(Privilege::READ))) {
            $sql
                ->where(Page::isStatusQuery(PageStatus::ID_PUBLIC))
                ->where(Page::publishedQuery());
        }

        return $this->releasedPages = Page::getDescription()
            ->getFactory()
            ->allExecute($this->getPagesQuery());
    }

    public function hasPage(Page $page): bool {
        $description = static::getDescription();

        $sql = Sql::select(self::TABLE_MENU_X_PAGES)
            ->where(Query::infer('id_menu = ? AND id_page = ?', $this->id, $page->id));

        return !is_null($sql->fetch($description->getConnection()));
    }

    public function addPage(Page $page): static {
        $description = static::getDescription();

        $sql = Sql::insert(self::TABLE_MENU_X_PAGES)
            ->columns(['id_menu', 'id_page'])
            ->value([Parameter::infer($this->id), Parameter::infer($page->id)]);

        $sql->run($description->getConnection());
        return $this;
    }

    public function deletePage(Page $page): static {
        $description = static::getDescription();

        $sql = Sql::delete(self::TABLE_MENU_X_PAGES)
            ->where(Query::infer('id_menu = ? AND id_page = ?', $this->id, $page->id));

        $sql->run($description->getConnection());
        return $this;
    }

    public function clearPages(): static {
        $description = static::getDescription();

        $sql = Sql::delete(self::TABLE_MENU_X_PAGES)
            ->where(Query::infer('id_menu = ?', $this->id));

        $sql->run($description->getConnection());
        return $this;
    }
}