<?php

namespace core;

trait Active {
    private bool $isActive = true;

    public function isActive(): bool {
        return $this->isActive;
    }

    public function enable(bool $force = true): self {
        $this->isActive = $force;
        return $this;
    }

    public function disable(): self {
        $this->enable(false);
        return $this;
    }
}