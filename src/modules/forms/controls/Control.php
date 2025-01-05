<?php

namespace modules\forms\controls;

use core\Render;
use modules\forms\Form;

interface Control extends Render {
    public function bind(Form $context): void;
    public function validate(?string $input, string &$reason): bool;
    public function getFieldName(): ?string;
}