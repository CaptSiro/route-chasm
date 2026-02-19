<?php

namespace components\core\fs;

use core\forms\controls\Control;
use core\forms\controls\FormControl;
use core\forms\controls\FormControlInfo;
use core\html\Attribute;
use core\html\HtmlAttribute;
use core\locale\LexiconUnit;
use core\view\Renderer;

class FileControl implements Control, Attribute {
    use Renderer, FormControl, FormControlInfo, HtmlAttribute, LexiconUnit;

    public const LEXICON_GROUP = 'form.fs.file-control';



    protected string $fileType;
    protected bool $multiple;



    /**
     * @param string $name
     * @param string $label
     * @param array<int> $fileHashes
     */
    public function __construct(
        protected string $name = self::class,
        protected string $label = self::class,
        protected array $fileHashes = []
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function multiple(): self {
        $this->multiple = true;
        return $this;
    }

    public function accept(string $type): self {
        $this->fileType = $type;
        return $this;
    }

    public function setValue(mixed $value): void {
        $this->fileHashes = $value;
    }

    public function stringifyFiles(): string {
        return implode(',', $this->fileHashes);
    }
}