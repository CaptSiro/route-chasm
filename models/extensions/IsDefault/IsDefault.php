<?php

namespace models\extensions\IsDefault;

interface IsDefault {
    public function isDefault(): bool;

    public function setAsDefault(): void;
}