<?php

namespace models\extensions\Enable;

use components\layout\Grid\description\GridColumn;
use core\database\sql\Column;
use modules\forms\description\Checkbox;

const ENABLE_PROPERTY = 'enabled';

/**
 * @property bool $enabled
 */
trait EnableExtension {
    public static function addEnableGridColumn(array &$columns): void {
        $columns[ENABLE_PROPERTY] = new GridColumn('Enabled', '96px');
    }



    #[Checkbox('Enabled')]
    #[GridColumn('Enabled', '96px')]
    #[Column('is_enabled', Column::TYPE_BOOLEAN)]
    protected bool $enabled;

    public function isEnabled(): bool {
        return $this->enabled;
    }

    public function enable(bool $enable = true): void {
        $this->enabled = $enable;
    }

    public function disable(): void {
        $this->enable(false);
    }
}