<?php

namespace core\database;

trait ColumnLabelOverride {
    protected ?string $label;

    public function overrideLabel(?string $label): static {
        $this->label = $label;
        return $this;
    }
}