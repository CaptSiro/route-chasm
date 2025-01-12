<?php

namespace modules\forms\controls\File;

use modules\forms\controls\Input\Input;

class File extends Input {
    public const FILE_TYPE_IMAGE = "image/*";

    protected string $fileType;
    protected bool $multiple;



    /**
     * @param string $name
     * @param string $label
     * @param string[] $files
     */
    public function __construct(
        string $name,
        string $label,
        protected array $files = []
    ) {
        parent::__construct("file", $name, $label);
        $this->setTemplate($this->getSource("File"));
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
}