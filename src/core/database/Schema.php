<?php

namespace core\database;

use components\layout\Table\TableLayout;
use modules\forms\definition\FormDefinition;

class Schema {
    protected ?string $entityClass;
    protected ?TableLayout $tableLayout = null;



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

    public function createDefaultTableLayout(): TableLayout {
        $layout = [];
        $columns = $this->table->getColumns();
        $idColumn = $this->table->getIdColumn();
        $overrides = $this->form->getOverrides();

        foreach ($columns as $name => $column) {
            if ($this->form->doDiscardIdColumn() && $name === $idColumn) {
                continue;
            }

            $definition = $overrides[$name] ?? $column->getFieldDefinition($name);
            if (!$definition->include()) {
                continue;
            }

            $layout[$definition->getLabel() ?? $name] = $name;
        }

        return new TableLayout($layout);
    }

    public function getTableLayout(): ?TableLayout {
        if (!isset($this->tableLayout)) {
            return $this->tableLayout = $this->createDefaultTableLayout();
        }

        return $this->tableLayout;
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

    public function getEntityFactory(): EntityFactory {
        return new ReflectionEntityFactory(
            $this->entityClass
        );
    }
}