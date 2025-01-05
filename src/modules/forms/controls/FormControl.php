<?php

namespace modules\forms\controls;

use modules\forms\Form;

trait FormControl {
    protected ?Form $context = null;

    public function bind(Form $context): void {
        $this->context = $context;
    }

    public function validate(?string $input, string &$reason): bool {
        return true;
    }

    public function getFieldName(): ?string {
        return null;
    }

    public function getId(): string {
        if (is_null($this->context)) {
            return $this->name;
        }

        return $this->context->createId($this->name);
    }
}