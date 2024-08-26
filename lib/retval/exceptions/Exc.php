<?php

namespace retval\exceptions;

use JsonSerializable;
use retval\Trace;

class Exc implements JsonSerializable {
    protected $message;
    protected $trace;



    public function __construct(string $msg) {
        $this->message = $msg;

        $this->bubbleUp();
    }



    public function getMessage(): string {
        return $this->message;
    }



    public function getTrace(): array {
        return $this->trace;
    }



    public function bubbleUp() {
        $this->trace = [];

        foreach (debug_backtrace() as $trace) {
            $this->trace[] = new Trace($trace["file"], $trace["line"]);
        }
    }



    function jsonSerialize(): object {
        return (object)[
            "error" => $this->message,
            "trace" => $this->trace,
        ];
    }
}