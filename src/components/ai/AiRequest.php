<?php

namespace components\ai;

use core\ResourceLoader;
use core\view\View;

class AiRequest implements View, \JsonSerializable {
    use ResourceLoader;



    /** @var array<string, mixed> */
    protected array $attributes;

    /** @var array<InputMessage> */
    protected array $messages;

    public function __construct(
        protected string $model
    ) {
        $this->attributes = [];
        $this->messages = [];
    }



    public function set(string $name, mixed $value): static {
        $this->attributes[$name] = $value;
        return $this;
    }

    public function add(InputMessage $message): static {
        $this->messages[] = $message;
        return $this;
    }



    // View
    public function render(): string {
        return json_encode($this);
    }

    public function getRoot(): View {
        return $this;
    }

    public function __toString(): string {
        return $this->render();
    }



    // JsonSerializable
    public function jsonSerialize(): array {
        return array_merge([
            "model" => $this->model,
            "input" => $this->messages
        ], $this->attributes);
    }
}