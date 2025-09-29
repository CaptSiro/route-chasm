<?php

namespace components\windows\LanguageSelect;

use components\core\Window\Window;
use core\locale\Lexicon;
use core\view\Component;

class LanguageSelect extends Component {
    public const LEXICON_GROUP = 'language.select';
    public const WINDOW_TITLE = 'Language select';



    public static function window(bool $openButton = true): Window {
        $title = Lexicon::translate(
            self::LEXICON_GROUP,
            self::WINDOW_TITLE
        );

        $window = new Window(new self(), $title, $openButton);
        $window->setFlag(Window::FLAG_DRAGGABLE);
        return $window;
    }
}