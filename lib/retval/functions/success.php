<?php

namespace retval;

function success($value): Result {
    return new Result(true, $value, null);
}