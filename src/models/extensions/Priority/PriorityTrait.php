<?php

namespace models\extensions\Priority;

use components\layout\Grid\description\GridColumn;
use core\database\sql\Column;

const PROPERTY_PRIORITY = 'priority';
const COLUMN_PRIORITY = PROPERTY_PRIORITY;

trait PriorityTrait {
    public static function addPriorityGridColumn(array &$columns): void {
        $columns[PROPERTY_PRIORITY] = new GridColumn('Priority', '96px');
    }



    #[Column(type: Column::TYPE_INTEGER)]
    public int $priority;



    public function getPriority(): int {
        return $this->priority;
    }

    public function setPriority(int $priority, bool $save = false): static {
        $this->priority = $priority;

        if ($save) {
            $this->save();
        }

        return $this;
    }
}