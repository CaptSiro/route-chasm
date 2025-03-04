<?php

namespace modules\forms\definition;

use core\database\TableDefinition;
use modules\forms\definition\overrides\FieldDefinition;
use modules\forms\definition\overrides\FieldOverride;
use modules\forms\Form;

class FormDefinition {
    protected TableDefinition $table;

    /**
     * @param array<FieldDefinition> $overrides
     * @param bool $discardIdColumn
     */
    public function __construct(
        protected array $overrides = [],
        protected bool $discardIdColumn = true
    ) {}



    public function link(TableDefinition $table): void {
        $this->table = $table;
    }

    /**
     * @return array<FieldOverride>
     */
    public function getOverrides(): array {
        return $this->overrides;
    }

    public function doDiscardIdColumn(): bool {
        return $this->discardIdColumn;
    }

    public function initForm(Form $form, array $data): void {
        $columns = $this->table->getColumns();
        $idColumn = $this->table->getIdColumn();

        foreach ($columns as $name => $column) {
            if ($this->discardIdColumn && $name === $idColumn) {
                continue;
            }

            $definition = $this->overrides[$name] ?? $column->getFieldDefinition($name);
            if (!$definition->include()) {
                continue;
            }

            $definition->setName($name);
            $form->add($definition->getComponent($data[$name] ?? null));
        }
    }
}