<?php

namespace core;

trait Flags {
    protected int $flags = 0;

    public function getFlags(): int {
        return $this->flags;
    }

    public function setFlag(int $flags): self {
        $this->flags |= $flags;
        return $this;
    }

    public function removeFlag(int $flag): self {
        $this->flags &= ~$flag;
        return $this;
    }

    public function hasFlag(int $flag): bool {
        return ($this->flags & $flag) !== 0;
    }
}