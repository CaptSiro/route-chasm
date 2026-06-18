<?php

namespace core\tf;

use core\view\View;

interface Assertion {
    public function result(): bool;

    public function error(): View;
}