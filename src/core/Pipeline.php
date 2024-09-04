<?php

namespace core;

interface Pipeline {
    /**
     * Advances forward the pipeline and returns next element. The element type is implementation depended
     * @return mixed
     */
    function next(): mixed;

    function current(): mixed;

    /**
     * Returns whether current element is still valid element of sequence or if it is end of pipeline.
     * Example would be C-style strings. The last element of the string is character <code>\0</code> that is stored
     * alongside the data, but it is not valid character for the string. This function basically asks whether the
     * current element is the <code>\0</code> character (in the C-style example)
     * @return bool
     */
    function isExhausted(): bool;
}