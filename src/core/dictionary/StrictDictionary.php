<?php

namespace core\dictionary;

interface StrictDictionary extends Dictionary {
    function getStrict(string $name): mixed;
}