<?php

namespace core\view;

use JsonSerializable;

class Json implements View, JsonSerializable {
    protected mixed $json;



    public function __construct(mixed $json = null) {
        $this->json = $json;
    }



    function render(): string {
        return json_encode($this);
    }

    public function jsonSerialize(): mixed {
        return $this->json;
    }

    public function __toString(): string {
        return json_encode($this);
    }

    public function getRoot(): View {
        return $this;
    }
}