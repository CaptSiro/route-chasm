<?php

namespace core\database;

trait ColumnNameOverride {
    protected ?string $name;

    public function overrideName(?string $name): static {
        $this->name = $name;
        return $this;
    }
}