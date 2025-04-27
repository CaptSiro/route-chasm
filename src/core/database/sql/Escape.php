<?php

namespace core\database\sql;

interface Escape {
    public function escapeTable(string $table): string;

    public function escapeColumn(string $column): string;
}