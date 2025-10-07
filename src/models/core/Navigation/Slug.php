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
use core\route\Route;
use core\view\View;
use models\core\Language\Language;

/**
 * @property int $parentId
 * @property int $contextId
 * @property int $languageId
 * @property string $slug
 * @property int $factoryId
 * @property string $data
 */

#[Table('core_navigation')]
#[Database(App::DATABASE)]
class Slug extends Model {
    public static function fromSlug(Language $language, int $contextId, string $slug, ?int $parentId = null): ?static {
        return self::fromSlugRaw($language->getId(), $contextId, $slug, $parentId);
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



    #[Column('id_slug', type: Column::TYPE_INTEGER, primaryKey: true)]
    protected int $id;

    #[Column('id_navigation_context', type: Column::TYPE_INTEGER)]
    protected int $contextId;

    #[Column('id_parent', type: Column::TYPE_INTEGER, nullable: true)]
    protected ?int $parentId;

    #[Column('id_language', type: Column::TYPE_INTEGER)]
    protected int $languageId;

    #[GridColumn]
    #[Column(type: Column::TYPE_STRING)]
    protected string $slug;

    #[Column('id_navigation_factory', type: Column::TYPE_INTEGER, nullable: true)]
    protected ?int $factoryId;

    #[Column(type: Column::TYPE_STRING)]
    protected string $data;



    public function setParentId(?int $parentId): static {
        return $this->set(['parentId' => $parentId]);
    }

    public function setLanguage(Language $language): static {
        return $this->set(['languageId' => $language->getId()]);
    }

    public function setFactory(NavigationFactory $factory, string $data): static {
        return $this->setFactoryRaw(
            NavigationFactoryRecord::fromName($factory->getName(), create: true)->getId(),
            $data
        );
    }

    public function setFactoryRaw(int $factoryId, string $data): static {
        return $this->set([
            'factoryId' => $factoryId,
            'data' => $data
        ]);
    }

    public function build(): View {
        return Navigator::build($this->factoryId, $this->data);
    }

//    public function getRoute(): Route {
//        $context =
//        $navigator =
//    }
}