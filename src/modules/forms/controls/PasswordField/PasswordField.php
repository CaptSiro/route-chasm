<?php

namespace modules\forms\controls\PasswordField;

use core\Attributes;
use core\CssClass;
use core\view\Renderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;
use modules\forms\controls\Input\Input;

class PasswordField implements Control {
    use Renderer, FormControl, CssClass, Attributes;

    protected Input $field;

    public function __construct(
        string $name,
        string $label,
        ?string $value = null,
        protected bool $addVisibilityControl = false
    ) {
        $this->field = new Input("password", $name, $label, $value);
    }
}