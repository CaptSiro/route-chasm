<?php

namespace modules\forms;

use core\html\JavascriptInit;

class FormAction {
    use JavascriptInit;



    public const TYPE_BUTTON = "button";
    public const TYPE_RESET = "reset";
    public const TYPE_SUBMIT = "submit";



    public static function submit(string $label = "Submit"): self {
        return new self(self::TYPE_SUBMIT, $label);
    }

    public function __construct(
        public readonly string $type,
        public readonly string $label,
    ) {}
}