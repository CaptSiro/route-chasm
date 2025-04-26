<?php

namespace modules\forms;

interface FormSection {
    public function add(Form $form, array $data): void;
}