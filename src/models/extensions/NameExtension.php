<?php

namespace models\extensions;

use components\layout\Grid\description\GridColumn;
use core\database\sql\Column;
use core\database\sql\query\Query;
use core\forms\description\TextField;

trait NameExtension {
    public static function fromName(string $name): ?static {
        return static::first(
            where: Query::infer('name = ?', $name)
        );
    }



    #[GridColumn]
    #[TextField]
    #[Column(type: Column::TYPE_STRING)]
    protected string $name;
}