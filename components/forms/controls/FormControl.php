<?php

namespace components\forms\controls;

use components\forms\Form;

trait FormControl {
    public function getId(): string {
        $current = Form::rendering();
        if (is_null($current)) {
            return $this->name;
        }

        return $current->createId($this->name);
    }
}