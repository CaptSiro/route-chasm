<?php

namespace models\extensions\Name;

use components\layout\Grid\description\GridColumn;
use core\database\sql\Column;
use core\database\sql\ModelDescription;
use core\database\sql\query\Query;
use core\forms\description\TextField;

trait NameExtension {
    public static function fromName(string $name): ?static {
        return static::first(
            where: Query::infer('name = ?', $name)
        );
    }

    public static function createOptions(Query|null|string $where = null): array {
        $ret = [];

        /** @var ModelDescription $description */
        $description = static::getDescription();
        $idColumn = $description->getIdColumn()->getName();

        foreach (static::all([$idColumn, 'name'], $where) as $model) {
            $ret[$model->getId()] = $model->name;
        }

        return $ret;
    }



    #[GridColumn]
    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    protected string $name;



    public function getName(): string {
        return $this->name;
    }
}