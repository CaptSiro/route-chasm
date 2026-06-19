<?php

namespace components\ai\Schema;

trait Description {
    public function setDescription(string $description): static {
        $this->set('description', $description);
        return $this;
    }
}