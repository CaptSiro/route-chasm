<?php

namespace modules\forms\controls\PasswordField;

use core\Attributes;
use core\CssClass;
use core\view\Renderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;
use modules\forms\controls\Input\Input;

/*
 * This whole class reeks of code smell... This should be reviewed
 */
class PasswordField implements Control {
    use Renderer, FormControl, CssClass, Attributes;

    protected Input $field;

    public function __construct(
        string $name = self::class,
        string $label = self::class,
        ?string $value = null,
        protected bool $addVisibilityControl = false
    ) {
        $this->field = new Input("password", $name, $label, $value);
    }

    public function setValue(mixed $value): void {
        $this->field->setValue($value);
    }

    public function setLabel(string $label): void {
        $this->field->setLabel($label);
    }

    public function setName(string $name): void {
        $this->field->setName($name);
    }
}