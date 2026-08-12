<?php

namespace components\SaveError;

use components\Message\MessageType;
use core\http\HttpCode;
use core\view\Component;
use core\view\Renderer;
use core\view\renderers\HtmlRenderer;

class SaveErrorGroup extends Component {
    /**
     * @param string $separator
     * @param array<SaveError> $errors
     * @return string
     */
    public static function joinMessages(string $separator, array $errors): string {
        return implode(
            $separator,
            array_map(fn($x) => $x->getMessage(), $errors)
        );
    }



    public function __construct(
        protected array $errors,
        protected int $code = HttpCode::CE_BAD_REQUEST,
        ?Renderer $renderer = new HtmlRenderer()
    ) {
        parent::__construct($renderer);
    }



    public function getErrors(): array {
        return $this->errors;
    }



    public function toText(): string {
        return self::joinMessages("\n", $this->errors);
    }

    public function jsonSerialize(): array {
        return [
            'type' => MessageType::ERROR,
            "group" => $this->errors,
        ];
    }
}