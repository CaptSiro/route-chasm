<?php

namespace components\forms\controls;

use components\html\Attribute;
use components\html\HtmlAttribute;
use core\locale\LexiconUnit;
use core\view\Renderer;

class Input implements Control, Attribute {
    use Renderer, FormControl, FormControlInfo, HtmlAttribute, LexiconUnit;

    public const LEXICON_GROUP = 'form.control';



    public function __construct(
        protected string $type,
        protected string $name,
        protected string $label = self::class,
        protected string $value = '',
    ) {
        $this->attributes = [];
        $this->setTemplate(self::getTemplateResourceSelf());
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function getFieldName(): ?string {
        return $this->name;
    }

    public function getLabel(): string {
        return $this->label;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function pattern(string $pattern): static {
        $this->addAttribute("pattern", $pattern);
        return $this;
    }

    public function required(): static {
        $this->addAttribute("required", true);
        return $this;
    }

    public function readonly(): static {
        $this->addAttribute('readonly');
        return $this;
    }
}