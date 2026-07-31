<?php

namespace components\fs;

use components\forms\controls\Control;
use components\forms\controls\FormControl;
use components\forms\controls\FormControlInfo;
use components\html\Attribute;
use components\html\HtmlAttribute;
use core\fs\variants\FileVariantTransformer;
use core\fs\variants\ImageVariant;
use core\locale\LexiconUnit;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;
use models\fs\File;
use models\fs\Shortcut;

class FileControl implements Control, Attribute, ViewTemplate {
    use ViewTemplateRenderer, FormControl, FormControlInfo, HtmlAttribute, LexiconUnit;

    public const LEXICON_GROUP = 'form.fs.file-control';



    public static function fromShortcut(string $name, string $label, ?Shortcut $shortcut = null): static {
        return (new static($name, $label))
            ->setFile($shortcut?->getFile());
    }




    protected string $fileType;
    protected bool $multiple;
    protected ?File $file = null;
    protected ?string $hash = null;


    /**
     * @param string $name
     * @param string $label
     */
    public function __construct(
        protected string $name = self::class,
        protected string $label = self::class,
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

    public function setFile(?File $file = null): static {
        $this->file = $file;
        $this->hash = $file?->hash;
        return $this;
    }

    public function setValue(mixed $value): void {
        $this->hash = $value;
    }

    public function getTransformer(): FileVariantTransformer {
        return ImageVariant::resolve(
            ImageVariant::TRANSFORMER_FILE_IMAGE_PREVIEW,
            400,
            300,
        );
    }
}