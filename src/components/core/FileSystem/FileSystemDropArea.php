<?php

namespace components\core\FileSystem;

use core\locale\LexiconUnit;
use core\view\Container;
use core\view\Renderer;
use core\view\View;
use models\core\fs\Directory;

class FileSystemDropArea implements Container {
    use Renderer, LexiconUnit;

    public const LEXICON_GROUP = 'file-system.drop-area';



    protected View $content;

    public function __construct(
        protected ?Directory $directory = null,
        protected ?string $acceptFileType = null,
        protected bool $readonly = false,
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function setDirectory(?Directory $directory): static {
        $this->directory = $directory;
        return $this;
    }

    public function setAcceptFileType(?string $acceptFileType): static {
        $this->acceptFileType = $acceptFileType;
        return $this;
    }

    public function setReadonly(bool $readonly): void {
        $this->readonly = $readonly;
    }



    // Container
    public function addContent(View $view): static {
        $this->content = $view;
        return $this;
    }
}