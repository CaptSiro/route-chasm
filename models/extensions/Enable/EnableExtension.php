<?php

namespace models\extensions\Enable;

use components\forms\controls\Control;
use components\forms\description\Checkbox;
use components\layout\Grid\description\GridColumn;
use core\database\sql\Column;

const PROPERTY_ENABLED = 'enabled';

trait EnableExtension {
    public static function addEnableGridColumn(array &$columns): void {
        $columns[PROPERTY_ENABLED] = new GridColumn('Enabled', '96px');
    }

    public static function getEnableControl(): Control {
        return new \components\forms\controls\Checkbox(PROPERTY_ENABLED, 'Enabled');
    }



    #[Checkbox('Enabled', isFirst: true)]
    #[GridColumn('Enabled', '96px', isFirst: true)]
    #[Column('is_enabled', Column::TYPE_BOOLEAN)]
    public bool $enabled;



    public function isEnabled(): bool {
        return $this->enabled;
    }

    public function enable(bool $enable = true): void {
        $this->enabled = $enable;
    }

    public function disable(): void {
        $this->enabled = false;
    }
}