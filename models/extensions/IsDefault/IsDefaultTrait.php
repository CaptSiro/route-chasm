<?php

namespace models\extensions\IsDefault;

use components\layout\Grid\description\GridColumn;
use core\database\sql\Column;
use core\database\sql\ModelDescription;
use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\Sql;

const PROPERTY_IS_DEFAULT = 'isDefault';

trait IsDefaultTrait {
    private static mixed $defaultModel = 0; // unset

    public static function getDefault(bool $override = false): ?static {
        if (static::$defaultModel === 0 || $override) {
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
    public bool $isDefault = false;



    public function isDefault(): bool {
        return $this->isDefault;
    }

    public function setAsDefault(): void {
        $description = ModelDescription::extract(static::class);
        Sql::update($description->getTable())
            ->set('is_default', Parameter::infer(false))
            ->where('1')
            ->run($description->getConnection());

        $this->isDefault = true;
        $this->save();
    }

    public function saveIsDefault(): void {
        if (!$this->isDefault()) {
            return;
        }

        $default = static::first(
            where: Query::infer("is_default = ?", true)
        );

        if ($default?->getId() === $this->getId()) {
            return;
        }

        $this->setAsDefault();
    }
}