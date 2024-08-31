<?php

namespace core\dictionary;

interface StrictDictionary extends Dictionary {
    /**
     * @param string $name
     * @return mixed
     */
    function getStrict(string $name): mixed;
}