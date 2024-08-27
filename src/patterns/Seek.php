<?php

namespace patterns;

interface Seek {
    function seek(int $offset): void;
    function skip(int $steps): void;
}