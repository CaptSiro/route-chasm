<?php

namespace core\forms\controls\PasswordField;

use core\forms\controls\Control;
use core\forms\controls\FormControl;
use core\forms\controls\Input\Input;
use core\html\Attribute;
use core\html\HtmlAttribute;
use core\view\Renderer;

/*
 * This whole class reeks of code smell... This should be reviewed
 */
class PasswordField implements Control, Attribute {
    use Renderer, FormControl, HtmlAttribute;

    protected Input $field;

    public function __construct(
        string $name = self::class,
        string $label = self::class,
        string $value = '',
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