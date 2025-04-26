<?php

namespace core\database_v3\sql;

interface Escape {
    public function escapeTable(string $table): string;

    public function escapeColumn(string $column): string;
}