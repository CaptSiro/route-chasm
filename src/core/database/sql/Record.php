<?php

namespace core\database\sql;

interface Record {
    public function save(): DatabaseAction;

    public function delete(): DatabaseAction;

    public function setOrigin(Origin $origin): void;
}