<?php

namespace models\core\Page;

use core\App;
use core\data\DataItem;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\DatabaseAction;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\forms\description\TextField;
use core\RouteChasmEnvironment;
use core\utils\Strings;
use models\core\Language\Language;
use models\core\Navigation\Slug;

#[Table('core_page_localization')]
#[Database(App::DATABASE)]
class PageLocalization extends Model {
    public static function fromPageRaw(int $pageId, int $languageId): ?static {
        return self::first(
            where: Query::infer('id_page = ? AND id_language = ?', $pageId, $languageId)
        );
    }

    public static function forPageRaw(int $pageId): array {
        return self::all(
            where: Query::infer('id_page = ?', $pageId)
        );
    }

    public static function createSlugLiteral(Language $language, string $title): string {
        return $language->getLocale()->formatUrlSegment($title);
    }



    #[Column('id_localized_page', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[Column('id_page', type: Column::TYPE_INTEGER)]
    public int $pageId;

    #[Column('id_language', type: Column::TYPE_INTEGER)]
    public int $languageId;

    #[Column('id_slug', type: Column::TYPE_INTEGER)]
    public int $slugId;

    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    public string $title;

    protected Page $page;
    protected ?PageMeta $meta;
    protected Slug $slug;
    protected Language $language;



    public function getHumanIdentifier(): string {
        return $this->title;
    }

    public function delete(): DatabaseAction {
        $this->getMeta()?->delete();
        $status = parent::delete();
        $this->getSlug()->delete();
        return $status;
    }



    public function getSlugLiteral(?Language $language = null): string {
        return self::createSlugLiteral(
            $language ?? Language::fromId($this->languageId),
            $this->title
        );
    }

    public function getLanguage(): Language {
        if (!isset($this->language)) {
            $this->language = Language::fromId($this->languageId);
        }

        return $this->language;
    }

    public function getPage(): Page {
        if (!isset($this->page)) {
            $this->page = Page::fromId($this->pageId);
        }

        return $this->page;
    }

    public function getSlug(): Slug {
        if (!isset($this->slug)) {
            $this->slug = Slug::fromId($this->slugId);
        }

        return $this->slug;
    }

    public function getMeta(): ?PageMeta {
        if (!isset($this->meta)) {
            $this->meta = PageMeta::fromLocalization($this);
        }

        return $this->meta;
    }

    public function setPage(Page $page): void {
        $this->pageId = $page->id;
        $this->page = $page;
    }

    public function setSlug(Slug $slug): void {
        $this->slugId = $slug->id;
        $this->slug = $slug;
    }

    public function get(string $item = ''): DataItem {
        $file = Strings::lpad('0', (string) $this->getPage()->id, RouteChasmEnvironment::ID_DIGITS);
        $file .= '_' . $this->getLanguage()->code;
        if (!empty($item)) {
            $file .= '_'. $item;
        }

        return new DataItem(
            Page::DATA_NAMESPACE,
            $file
        );
    }
}