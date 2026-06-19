<?php

namespace core\actions;

use core\utils\Objects;

trait ActorClassName {
    public function getActorName(): string {
        return Objects::getClass($this);
    }
}