<?php

namespace core\collections;

use core\collections\dictionary\NotDefinedException;

/**
 * @template T
 * @template-implements Dictionary<T>
 */
interface StrictDictionary extends Dictionary {
    /**
     * @param string $name
     * @return T
     *
     * @throws NotDefinedException
     */
    public function getStrict(string $name): mixed;
}