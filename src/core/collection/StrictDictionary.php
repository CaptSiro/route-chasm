<?php

namespace core\collection;

interface StrictDictionary extends Dictionary {
    public function getStrict(string $name): mixed;
}