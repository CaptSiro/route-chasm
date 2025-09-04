<?php

namespace models\extensions\IsDefault;

use components\layout\Grid\description\GridColumn;
use core\database\sql\Column;
use core\database\sql\ModelDescription;
use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\Sql;

const IS_DEFAULT_PROPERTY = 'default';

/**
 * @property bool $default
 */
trait IsDefaultExtension {
    public static function getDefault(): ?static {
        return static::first(
            where: Query::infer("is_default = ?", true)
        );
    }

    public static function addIsDefaultGridColumn(array &$columns): void {
        $columns[IS_DEFAULT_PROPERTY] = new GridColumn('Is Default', '96px');
    }



    #[Column('is_default', Column::TYPE_BOOLEAN)]
    protected bool $default;

    public function isDefault(): bool {
        return $this->default;
    }

    public function setAsDefault(): void {
        $description = ModelDescription::extract(static::class);
        Sql::update($description->getEscapedTable())
            ->set('default', Parameter::infer(false))
            ->run($description->connection);

        $this->set(['default' => true]);
        $this->save();
    }
}