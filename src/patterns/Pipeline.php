<?php

namespace patterns;

interface Pipeline {
    function next(): bool;
    function current(): mixed;
    function isExhausted(): bool;
}