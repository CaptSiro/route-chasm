<?php

namespace components\forms\controls;

class HiddenField extends Input {
    public function __construct(
        string $name = self::class,
        ?string $value = null
    ) {
        parent::__construct('hidden', $name, '', $value ?? '');
        $this->addCssClass('hide');
    }
}