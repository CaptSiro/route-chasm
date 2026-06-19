<?php

namespace core\translation;

/**
 * @template T
 */
interface Translator {
    /**
     * @param T $source
     * @return T target
     */
    public function add(mixed $source): mixed;

    /**
     * @param T $source
     * @return T target
     */
    public function getTarget(mixed $source): mixed;

    /**
     * @param T $target
     * @return T source
     */
    public function getSource(mixed $target): mixed;
}