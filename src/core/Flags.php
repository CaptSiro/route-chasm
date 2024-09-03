<?php

namespace core;

trait Flags {
    protected int $flags = 0;

    public function getFlags(): int {
        return $this->flags;
    }

    /**
     * To set more than one flag use the bitwise-or operator <code>|</code>
     */
    public function setFlag(int $flags): self {
        $this->flags |= $flags;
        return $this;
    }

    /**
     * To remove more than one flag use the bitwise-or operator <code>|</code>
     */
    public function removeFlag(int $flags): self {
        $this->flags &= ~$flags;
        return $this;
    }

    /**
     * To check more than one flag use the bitwise-or operator <code>|</code>
     */
    public function hasFlag(int $flags): bool {
        return ($this->flags & $flags) !== 0;
    }
}