<?php

namespace core\view;

class StringRenderer extends Component {
    public function __construct(
        protected string $string,
        bool $isMiddleware = false
    ) {
        parent::__construct($isMiddleware);
    }

    public function __toString(): string {
        return $this->string;
    }



    public function render(): string {
        return $this->string;
    }

    public function getRoot(): View {
        return $this;
    }

    public function isMiddleware(): bool {
        return false;
    }
}