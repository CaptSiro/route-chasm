<?php

namespace core\database;

use modules\forms\definition\FormDefinition;

class Schema {
    protected ?string $entityClass;



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

    public function bindEntityClass(string $class): void {
        $this->entityClass = $class;
    }

    public function create(array $data): Entity {
        $entity = new $this->entityClass();

        foreach ($this->table->getColumns() as $name => $column) {
            if (isset($data[$name])) {
                $entity->$name = $data[$name];
            }
        }

        return $entity;
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