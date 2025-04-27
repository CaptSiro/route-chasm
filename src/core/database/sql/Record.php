<?php

namespace core\database\sql;

interface Record {
    public function save(): Action;

    public function delete(): Action;

    public function setOrigin(Origin $origin): void;
}