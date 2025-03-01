<?php

namespace sptf;

enum TestOutcome: string {
    case FAILED = "FAIL";
    case NONE = "NONE";
    case PASSED = "PASS";

    public static function fromStats(int $passed, int $failed): self {
        if ($failed !== 0) {
            return self::FAILED;
        }

        if ($passed === 0) {
            return self::NONE;
        }

        return self::PASSED;
    }
}
