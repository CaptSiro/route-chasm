<?php

namespace core\translation;

interface Translator {
    public function add(mixed $source): mixed;

    public function getTarget(mixed $source): mixed;

    public function getSource(mixed $target): mixed;
}