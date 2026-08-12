<?php

namespace components\fs;

use core\locale\LexiconUnit;
use core\view\Container;
use core\view\ContainerTrait;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;
use models\fs\Directory;

class FileSystemDropArea implements Container, ViewTemplate {
    use ViewTemplateRenderer, ContainerTrait, LexiconUnit;

    public const LEXICON_GROUP = 'file-system.drop-area';



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
}