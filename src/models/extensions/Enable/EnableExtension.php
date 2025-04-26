<?php

namespace models\extensions\Enable;

use core\database_v3\sql\Column;

/**
 * @property bool $enabled
 */
trait EnableExtension {
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