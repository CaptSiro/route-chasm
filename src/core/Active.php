<?php

namespace core;

trait Active {
    private bool $isActive = true;

    public function isActive(): bool {
        return $this->isActive;
    }

    public function activate(bool $force = true): self {
        $this->isActive = $force;
        return $this;
    }

    public function deactivate(): self {
        $this->activate(false);
        return $this;
    }
}