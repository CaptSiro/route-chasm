<?php

namespace retval;

use Closure;
use JsonSerializable;
use retval\exceptions\Exc;
use retval\exceptions\NullPointerExc;

require_once __DIR__ . "/exceptions/Exc.php";



class Result implements JsonSerializable {
    protected $success, $failure, $isSuccess;



    public function __construct($isSuccess, $success, ?Exc $failure) {
        $this->isSuccess = $isSuccess;
        $this->success = $success;
        $this->failure = $failure;
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



    public function getFailure(): Exc {
        return $this->failure;
    }



    public function getSuccess() {
        return $this->success;
    }



    public function succeeded(Closure $function): Result {
        if ($this->isSuccess) {
            return success($function($this->success));
        }

        return fail($this->failure);
    }



    public function isSuccess(): bool {
        return $this->isSuccess;
    }



    public function failed(Closure $function): Result {
        if ($this->isFailure()) {
            return fail($function($this->failure));
        }

        return success($this->success);
    }



    public function isFailure(): bool {
        return !$this->isSuccess;
    }



    public function either(Closure $successFunction, Closure $failFunction): Result {
        if ($this->isSuccess) {
            return success($successFunction($this->success));
        }

        return fail($failFunction($this->failure));
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