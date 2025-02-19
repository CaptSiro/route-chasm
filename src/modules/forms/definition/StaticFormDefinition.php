<?php

namespace modules\forms\definition;

trait StaticFormDefinition {
    protected static FormDefinition $form;

    public static function getFormDefinition(): FormDefinition {
        return self::$form;
    }
}