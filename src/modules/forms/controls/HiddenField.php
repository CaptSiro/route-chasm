<?php

namespace modules\forms\controls;

use modules\forms\controls\Input\Input;

class HiddenField extends Input {
    public function __construct(
        string $name = self::class,
        ?string $value = null
    ) {
        parent::__construct('hidden', $name, $label = null, $value);
        $this->addCssClass('hide');
    }
}