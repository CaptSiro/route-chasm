<?php

namespace core\view;

use JsonSerializable;

class json extends Component implements View, JsonSerializable {
    public static function view(mixed $json): View {
        return new self($json);
    }



    public function __construct(
        protected mixed $json
    ) {
        parent::__construct();
    }



    // View
    public function render(): string {
        return json_encode($this->json);
    }

    public function __toString(): string {
        return $this->render();
    }



    // JsonSerializable
    public function jsonSerialize(): mixed {
        return $this->json;
    }
}