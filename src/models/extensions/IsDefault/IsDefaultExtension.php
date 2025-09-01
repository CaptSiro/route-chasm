<?php

namespace models\extensions\IsDefault;

use core\database\sql\Column;
use core\database\sql\ModelDescription;
use core\database\sql\query\Parameter;
use core\database\sql\query\Query;
use core\database\sql\Sql;

/**
 * @property bool $default
 */
trait IsDefaultExtension {
    public static function getDefault(): ?static {
        return static::first(
            where: Query::infer("is_default = ?", [true])
        );
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