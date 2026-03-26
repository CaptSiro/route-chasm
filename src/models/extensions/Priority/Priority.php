<?php

namespace models\extensions\Priority;

interface Priority {
    public function getPriority(): int;
    public function setPriority(int $priority, bool $save = false): static;
}