<?php

namespace core\forms\controls;

use core\forms\Form;

trait FormControl {
    public function getId(): string {
        $current = Form::rendering();
        if (is_null($current)) {
            return $this->name;
        }

        return $current->createId($this->name);
    }
}