<?php

namespace models\extensions\Editable;

interface Editable {
    public function isEditable(): bool;

    public function setEditable(bool $editable): void;
}