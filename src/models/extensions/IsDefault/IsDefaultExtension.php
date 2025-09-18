<?php

namespace models\extensions\IsDefault;

use components\layout\Grid\description\GridColumn;
use core\database\sql\Column;
use core\database\sql\ModelDescription;
use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\Sql;

const PROPERTY_IS_DEFAULT = 'default';

/**
 * @property bool $default
 */
trait IsDefaultExtension {
    private static mixed $defaultModel;

    public static function getDefault(bool $override = false): ?static {
        if (!isset(static::$defaultModel) || $override) {
            static::$defaultModel = static::first(
                where: Query::infer("is_default = ?", true)
            );
        }

        return static::$defaultModel;
    }

    public static function addIsDefaultGridColumn(array &$columns): void {
        $columns[PROPERTY_IS_DEFAULT] = new GridColumn('Is Default', '96px');
    }



    #[Column('is_default', Column::TYPE_BOOLEAN)]
    protected bool $default;

    public function isDefault(): bool {
        return $this->default;
    }

    public function setAsDefault(): void {
        $description = ModelDescription::extract(static::class);
        Sql::update($description->getEscapedTable())
            ->set(PROPERTY_IS_DEFAULT, Parameter::infer(false))
            ->run($description->connection);

        $this->set([PROPERTY_IS_DEFAULT => true]);
        $this->save();
    }
}