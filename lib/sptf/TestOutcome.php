<?php

namespace sptf;

enum TestOutcome: string {
    case FAILED = "FAIL";
    case NONE = "NONE";
    case PASSED = "PASS";

    public static function fromStats(int $passed, array $failed): self {
        if (!empty($failed)) {
            return self::FAILED;
        }

        if ($passed === 0) {
            return self::NONE;
        }

        return self::PASSED;
    }
}
