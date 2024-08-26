<?php

namespace retval;

use retval\exceptions\Exc;

function fail(Exc $exception): Result {
    return new Result(false, null, $exception);
}