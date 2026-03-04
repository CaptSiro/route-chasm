<?php

namespace models\extensions\Enable;

use components\layout\Grid\description\GridColumn;
use core\database\sql\Column;
use core\forms\controls\Control;
use core\forms\description\Checkbox;

const PROPERTY_ENABLED = 'enabled';

trait EnableExtension {
    public static function addEnableGridColumn(array &$columns): void {
        $columns[PROPERTY_ENABLED] = new GridColumn('Enabled', '96px');
    }

    public static function getEnableControl(): Control {
        return new \core\forms\controls\Checkbox\Checkbox(PROPERTY_ENABLED, 'Enabled');
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