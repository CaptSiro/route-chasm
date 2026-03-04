<?php

namespace models\extensions\Name;

use components\layout\Grid\description\GridColumn;
use core\database\sql\Column;
use core\database\sql\ModelCache;
use core\database\sql\ModelDescription;
use core\database\sql\query\Query;
use core\forms\description\TextField;

trait NameExtension {
    use ModelCache;

    public static function fromName(string $name): ?static {
        if (!is_null($hit = static::modelCache_get($name))) {
            return $hit;
        }

        return static::modelCache_set($name, static::first(
            where: Query::infer('name = ?', $name)
        ));
    }

    public static function createOptions(Query|null|string $where = null): array {
        $ret = [];

        /** @var ModelDescription $description */
        $description = static::getDescription();
        $idColumn = $description->getIdColumn()->getName();

        foreach (static::all([$idColumn, 'name'], $where) as $model) {
            $ret[$model->id] = $model->name;
        }

        return $ret;
    }



    #[GridColumn]
    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    public string $name;



    public function getHumanIdentifier(): string {
        return $this->name;
    }

    public function getName(): string {
        return $this->name;
    }
}