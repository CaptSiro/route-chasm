<?php

namespace components\core\Window;

use components\core\Html\Html;
use core\Flags;
use core\html\Attribute;
use core\html\HtmlAttribute;
use core\utils\Strings;
use core\view\Renderer;
use core\view\View;

class Window implements View, Attribute {
    use Renderer, HtmlAttribute, Flags;

    public const FLAG_MINIMIZABLE = 1;
    public const FLAG_DRAGGABLE = 2;
    public const FLAG_DESTROY_ON_CLOSE = 8;
    // todo
//    public const FLAG_RESIZEABLE = 4;



    public static function createWindowOpener(Window $window, string $label, array $attributes = []): string {
        $id = $window->getId();

        return Html::wrapUnsafe(
            'button',
            $label,
            array_merge($attributes, ["onclick" => "window_open($('#$id'))"])
        );
    }



    protected static int $idLength = 4;
    protected static array $ids;
    public static function generateId(): string {
        $id = '';

        do {
            $id = Strings::randomBase64(self::$idLength);
            if (!isset(self::$ids[$id])) {
                break;
            }

            self::$idLength++;
        } while (true);

        self::$ids[$id] = $id;
        return $id;
    }



    protected string $id;

    public function __construct(
        protected View $content,
        protected string $title = '',
        protected bool $openButton = true
    ) {
        $this->addJavascriptInit('window_init');
        $this->id = 'window_'. self::generateId();
    }

    public function getId(): string {
        return $this->id;
    }
}