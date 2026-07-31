<?php

namespace components\windows;

use components\html\Attribute;
use components\html\HtmlAttribute;
use core\Flags;
use core\locale\LexiconUnit;
use core\utils\Strings;
use core\view\Html;
use core\view\View;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class Window implements ViewTemplate, Attribute {
    use ViewTemplateRenderer, HtmlAttribute, Flags, LexiconUnit;

    public const LEXICON_GROUP = 'window';

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
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }

    public function getId(): string {
        return $this->id;
    }
}