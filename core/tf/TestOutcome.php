<?php

namespace core\tf;

use JsonSerializable;

enum TestOutcome: string implements JsonSerializable {
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



    public function jsonSerialize(): string {
        return $this->value;
    }
}
