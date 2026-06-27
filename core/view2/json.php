<?php

namespace core\view2;

use JsonSerializable;

class json implements View, JsonSerializable {
    public static function view(mixed $json): View {
        return new self($json);
    }



    public function __construct(
        protected mixed $json
    ) {}



    // View
    public function render(): string {
        return json_encode($this->json);
    }

    public function __toString(): string {
        return $this;
    }



    // JsonSerializable
    public function jsonSerialize(): mixed {
        return $this;
    }
}