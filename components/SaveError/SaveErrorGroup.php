<?php

namespace components\SaveError;

use components\Message\MessageType;
use core\App;
use core\http\HttpCode;
use core\view\FormatAble;
use core\view\FormatAbleTrait;
use core\view\Formatter;
use core\view\ViewTemplate;

class SaveErrorGroup implements ViewTemplate, FormatAble {
    use FormatAbleTrait;

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
        protected int $code = HttpCode::CE_BAD_REQUEST
    ) {
        $this->setFormatter(Formatter::default($this));
    }



    public function getErrors(): array {
        return $this->errors;
    }



    // FormatAble
    public function render(): string {
        App::getInstance()
            ->getResponse()
            ->setStatus($this->code);

        return $this->renderFormatter();
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