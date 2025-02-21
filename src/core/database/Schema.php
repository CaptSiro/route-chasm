<?php

namespace core\database;

use modules\forms\definition\FormDefinition;

class Schema {
    /**
     * @param TableDefinition $table
     * @param FormDefinition|null $form
     * @param array<Extension> $extensions
     */
    public function __construct(
        protected TableDefinition $table,
        protected ?FormDefinition $form = null,
        protected array $extensions = []
    ) {
        if (is_null($this->form)) {
            $this->form = new FormDefinition();
        }

        $this->form->link($this->table);

        foreach ($this->extensions as $extension) {
            $extension->modifyTable($this->table);
        }
    }

    public function getTable(): TableDefinition {
        return $this->table;
    }

    public function getForm(): FormDefinition {
        return $this->form;
    }

    public function getExtension(string $class): ?Extension {
        foreach ($this->extensions as $extension) {
            if (get_class($extension) === $class) {
                return $extension;
            }
        }

        return null;
    }
}