<?php

namespace core\database;

use modules\forms\definition\FormDefinition;

class Schema {
    public function __construct(
        protected TableDefinition $table,
        protected ?FormDefinition $form = null
    ) {
        if (is_null($this->form)) {
            $this->form = new FormDefinition();
        }

        $this->form->link($this->table);
    }

    public function getTable(): TableDefinition {
        return $this->table;
    }

    public function getForm(): FormDefinition {
        return $this->form;
    }
}