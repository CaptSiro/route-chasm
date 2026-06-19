<?php

namespace core;

interface Identifier {
    public function getId(): mixed;

    public function getMachineIdentifier(): string;

    public function getHumanIdentifier(): string;
}