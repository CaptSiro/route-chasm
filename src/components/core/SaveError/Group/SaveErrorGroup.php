<?php

namespace components\core\SaveError\Group;

use components\core\SaveError\SaveError;
use core\App;
use core\communication\Format;
use core\http\HttpCode;
use core\view\Formatter;
use core\view\Renderer;
use core\view\View;

class SaveErrorGroup implements View {
    use Renderer;

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



    protected Formatter $formatter;

    public function __construct(
        protected array $errors,
        protected int $code = HttpCode::CE_BAD_REQUEST
    ) {
        $this->formatter = new Formatter(fn($type) => match ($type) {
            Format::IDENT_HTML => $this->renderTemplated(),
            Format::IDENT_XML => $this->renderTemplated($this->getResource("SaveErrorGroup.xml.phtml")),
            Format::IDENT_JSON => json_encode([
                "isError" => true,
                "group" => $this->errors,
            ]),
            default => self::joinMessages("\n", $this->errors)
        });
    }

    public function render(): string {
        App::getInstance()
            ->getResponse()
            ->setStatus($this->code);

        return $this->formatter->render();
    }
}