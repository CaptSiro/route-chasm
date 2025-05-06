<?php

namespace core\forms;

use core\database\sql\Model;

interface FormSection {
    public function add(Form $form, ?Model $model): void;
}