<?php

namespace modules\forms\controls;

use modules\forms\controls\Input\Input;

class HiddenField extends Input {
    public function __construct(
        string $name = self::class,
        string $value = ''
    ) {
        parent::__construct('hidden', $name, '', $value);
        $this->addCssClass('hide');
    }
}