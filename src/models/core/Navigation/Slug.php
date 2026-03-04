<?php

namespace models\core\Navigation;

use components\layout\Grid\description\GridColumn;
use core\App;
use core\database\sql\Column;
use core\database\sql\Database;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Table;
use core\navigation\NavigationFactory;
use core\navigation\Navigator;
use core\view\Component;
use models\core\Language\Language;

#[Table('core_navigation')]
#[Database(App::DATABASE)]
class Slug extends Model {
    public static function fromSlug(Language $language, int $contextId, string $slug, ?int $parentId = null): ?static {
        return self::fromSlugRaw($language->id, $contextId, $slug, $parentId);
    }

    public static function fromSlugRaw(int $languageId, int $contextId, string $slug, ?int $parentId = null): ?static {
        if (is_null($parentId)) {
            return static::first(
                where: Query::infer(
                    'id_navigation_context = ? AND id_parent IS NULL AND id_language = ? AND slug = ?',
                    $contextId,
                    $languageId,
                    $slug
                )
            );
        }

        return static::first(
            where: Query::infer(
                'id_navigation_context = ? AND id_parent = ? AND id_language = ? AND slug = ?',
                $contextId,
                $parentId,
                $languageId,
                $slug
            )
        );
    }



    #[Column('id_slug', type: Column::TYPE_INTEGER, isPrimaryKey: true)]
    public int $id;

    #[Column('id_navigation_context', type: Column::TYPE_INTEGER)]
    public int $contextId;

    #[Column('id_parent', type: Column::TYPE_INTEGER, nullable: true)]
    public ?int $parentId;

    #[Column('id_language', type: Column::TYPE_INTEGER)]
    public int $languageId;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    public string $slug;

    #[Column('id_navigation_factory', type: Column::TYPE_INTEGER, nullable: true)]
    public ?int $factoryId;

    #[Column(type: Column::TYPE_STRING)]
    public string $data;



    public function setParentId(?int $parentId): static {
        $this->parentId = $parentId ?? 0;
        return $this;
    }

    public function setLanguage(Language $language): static {
        $this->languageId = $language->id;
        return $this;
    }

    public function setFactory(NavigationFactory $factory, string $data): static {
        return $this->setFactoryRaw(
            NavigationFactoryRecord::fromName($factory->getName(), create: true)->id,
            $data
        );
    }

    public function setFactoryRaw(int $factoryId, string $data): static {
        $this->factoryId = $factoryId;
        $this->data = $data;
        return $this;
    }

    public function build(): Component {
        return Navigator::build($this->factoryId, $this->data);
    }
}