<?php

namespace core\forms\controls;

use core\forms\controls\Input\Input;

class HiddenField extends Input {
    public function __construct(
        string $name = self::class,
        ?string $value = null
    ) {
        parent::__construct('hidden', $name, '', $value ?? '');
        $this->addCssClass('hide');
    }
}