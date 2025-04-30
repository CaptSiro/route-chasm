<?php

namespace core\forms;

interface FormSection {
    public function add(Form $form, array $data): void;
}