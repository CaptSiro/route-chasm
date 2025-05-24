<?php

namespace core\forms\controls\File;

use core\forms\controls\Input\Input;

class File extends Input {
    public const FILE_TYPE_IMAGE = "image/*";

    protected string $fileType;
    protected bool $multiple;



    /**
     * @param string $name
     * @param string $label
     * @param array<string> $files
     */
    public function __construct(
        string $name = self::class,
        string $label = self::class,
        protected array $files = []
    ) {
        parent::__construct("file", $name, $label);
        $this->setTemplate($this->getResource("File"));
    }



    public function multiple(): self {
        $this->addAttribute("multiple");
        $this->multiple = true;
        return $this;
    }

    public function acceptImages(): self {
        $this->accept(self::FILE_TYPE_IMAGE);
        return $this;
    }

    public function accept(string $type): self {
        $this->fileType = $type;
        $this->addAttribute("accept", $type);
        return $this;
    }

    public function setValue(mixed $value): void {
        $this->files = $value;
    }
}