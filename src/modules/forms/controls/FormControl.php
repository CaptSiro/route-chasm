<?php

namespace modules\forms\controls;

use modules\forms\Form;

trait FormControl {
    public function getId(): string {
        $current = Form::rendering();
        if (is_null($current)) {
            return $this->name;
        }

        return $current->createId($this->name);
    }
}