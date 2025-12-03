<?php

namespace retval;

use Closure;
use JsonSerializable;
use retval\exceptions\Exc;
use retval\exceptions\NullPointerExc;



class Result implements JsonSerializable {
    public static function fail(Exc $exception): self {
        return new Result(false, null, $exception);
    }

    public static function success(mixed $value): self {
        return new Result(true, $value, null);
    }

    public static function all(Result ...$results): ResultSet {
        if (empty($results)) {
            return new ResultSet(null, [new NullPointerExc("Working with 0 results. You must pass at least one.")]);
        }

        $failed = [];
        $succeeded = [];

        foreach ($results as $result) {
            if ($result->isFailure()) {
                $failed[] = $result->getFailure();
            } else {
                $succeeded[] = $result->getSuccess();
            }
        }

        return new ResultSet($succeeded, $failed);
    }



    protected bool $isSuccess;
    protected mixed $success;
    protected ?Exc $failure;



    public function __construct($isSuccess, $success, ?Exc $failure) {
        $this->isSuccess = $isSuccess;
        $this->success = $success;
        $this->failure = $failure;
    }



    public function getFailure(): Exc {
        return $this->failure;
    }

    public function getSuccess() {
        return $this->success;
    }

    public function succeeded(Closure $function): Result {
        if ($this->isSuccess) {
            return self::success($function($this->success));
        }

        return self::fail($this->failure);
    }

    public function isSuccess(): bool {
        return $this->isSuccess;
    }

    public function failed(Closure $function): Result {
        if ($this->isFailure()) {
            return self::fail($function($this->failure));
        }

        return self::success($this->success);
    }

    public function isFailure(): bool {
        return !$this->isSuccess;
    }

    public function either(Closure $successFunction, Closure $failFunction): Result {
        if ($this->isSuccess) {
            return self::success($successFunction($this->success));
        }

        return self::fail($failFunction($this->failure));
    }

    public function strip(Closure $failFunction) {
        if (!$this->isSuccess) {
            return $failFunction($this->failure);
        }

        return $this->success;
    }

    public function jsonSerialize(): object {
        return (object)[
            "isSuccess" => $this->isSuccess,
            "success" => $this->success,
            "failure" => $this->failure,
        ];
    }
}