<?php

namespace components;

use core\locale\LexiconUnit;
use core\view\Component;
use core\view\Renderer;
use core\view\renderers\HtmlRenderer;

class CallStack extends Component {
    use LexiconUnit;

    public const LEXICON_GROUP = 'callstack';



    protected array $stack;

    public function __construct(
        int $remove = 0,
        ?Renderer $renderer = new HtmlRenderer()
    ) {
        parent::__construct($renderer);

        $this->setLexiconGroup(self::LEXICON_GROUP);
        $this->stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $count = max($remove, 0) + 1;
        for ($i = 0; $i < $count; $i++) {
            array_shift($this->stack);
        }
    }



    public function getEntryClass(array $entry): string {
        if (!isset($entry['class'])) {
            return '';
        }

        return $entry['class'] . $entry['type'];
    }

    public function jsonSerialize(): array {
        return $this->stack;
    }
}