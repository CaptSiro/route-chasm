<?php

namespace core\actions;

use core\utils\Objects;

trait ClassActorName {
    public function getActorName(): string {
        return Objects::getClass($this);
    }
}