<?php

namespace modules\forms\controls;

use modules\forms\controls\Input\Input;

class File extends Input {
    public function __construct(string $name, string $label, ?string $value = null) {
        parent::__construct("file", $name, $label, $value);
    }
}